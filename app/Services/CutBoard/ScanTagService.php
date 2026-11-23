<?php

namespace App\Services\CutBoard;

use App\Models\CutJob;
use App\Models\CutJobHeader;
use App\Models\CutJobLine;
use Illuminate\Validation\ValidationException;

class ScanTagService
{
    public function __construct(
        private readonly SourceSpecResolver $resolver,
        private readonly LineMasterDataService $masterData,
    ) {}

    public function scan(CutJob $job, CutJobHeader $header, array $data, int|string|null $userId): CutJobLine
    {
        $connection = config('cutboard.connection');

        try {
            $tagCode = TagCode::parse($data['raw_scan_code']);
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['raw_scan_code' => $exception->getMessage()]);
        }

        $jobLine = (int) preg_replace('/\D+/', '', (string) $job->production_line);
        $legacyLineFourForLineThree = $job->production_line === 'L3' && $tagCode->line === '4';
        if ($jobLine !== (int) $tagCode->line && ! $legacyLineFourForLineThree) {
            throw ValidationException::withMessages(['raw_scan_code' => 'QR นี้เป็นคนละไลน์ผลิตกับ Job']);
        }

        $resolved = $this->resolver->resolve($tagCode, $data);
        if ($resolved['spec_code'] === '') {
            throw ValidationException::withMessages(['spec_code' => 'ไม่พบ Spec Code จาก QR หรือข้อมูลต้นทาง']);
        }
        $firstJobSpecCode = CutJobLine::on($connection)
            ->where('cut_job_id', $job->id)
            ->where('source_type', 'WIP')
            ->whereNotNull('spec_code')
            ->value('spec_code');
        $expectedSpecCode = trim((string) ($header->product_type_code ?: $firstJobSpecCode ?: ''));
        if ($expectedSpecCode !== '' && strcasecmp($expectedSpecCode, $resolved['spec_code']) !== 0) {
            throw ValidationException::withMessages(['raw_scan_code' => 'ชนิดสินค้าของ WIP ต้องตรงกับ Header และรายการ WIP แรกของ Job']);
        }
        if ($resolved['qty'] <= 0) {
            throw ValidationException::withMessages(['qty' => 'ไม่พบจำนวนที่ถูกต้องจาก QR']);
        }

        $payload = [
            ...$resolved,
            'raw_scan_code' => $data['raw_scan_code'],
            'interface_qr' => $tagCode->isBx()
                ? $tagCode->interfaceQr($resolved['brand_code'], $resolved['product_type_code'])
                : null,
            'tag_date' => $tagCode->isBx()
                ? $tagCode->tagDate($resolved['brand_code'], $resolved['product_type_code'])
                : null,
            'tag' => $tagCode->isBx()
                ? $tagCode->tag($resolved['brand_code'], $resolved['product_type_code'])
                : null,
        ];

        if ($resolved['source_type'] === 'WIP') {
            $employeeGroup = $this->masterData->activeGroup($job->production_line, (int) ($data['employee_group_id'] ?? 0));
            if (! $employeeGroup) {
                throw ValidationException::withMessages(['employee_group_id' => 'เลือกกลุ่มพนักงานที่ยังเปิดใช้งานของไลน์นี้']);
            }
            $payload['employee_group_id'] = $employeeGroup->eg_id;
            $payload['employee_group_name'] = trim(($employeeGroup->employeeOne?->ue_name ?? '').' - '.($employeeGroup->employeeTwo?->ue_name ?? ''));
        } else {
            $payload['employee_group_id'] = null;
            $payload['employee_group_name'] = null;
        }

        if ((int) $header->cut_job_id !== (int) $job->id) {
            throw ValidationException::withMessages(['header_id' => 'Header ไม่ได้อยู่ใน Job นี้']);
        }

        return CutJob::on($connection)->getConnection()->transaction(function () use ($connection, $job, $header, $payload, $userId): CutJobLine {
            $lockedJob = CutJob::on($connection)->lockForUpdate()->findOrFail($job->id);
            $lockedHeader = CutJobHeader::on($connection)->lockForUpdate()->findOrFail($header->id);
            if (! in_array($lockedJob->status, ['draft', 'scanning'], true)) {
                throw ValidationException::withMessages(['job_id' => 'Job นี้จบการคัดหรือปิดแล้ว ไม่สามารถ Scan เพิ่มได้']);
            }
            if (! in_array($lockedHeader->status, ['draft', 'scanning'], true)) {
                throw ValidationException::withMessages(['header_id' => 'Header นี้ปิดแล้ว ไม่สามารถ Scan เพิ่มได้']);
            }
            if ($payload['source_type'] === 'WIP') {
                $firstSpecCode = CutJobLine::on($connection)
                    ->where('cut_job_id', $lockedJob->id)
                    ->where('source_type', 'WIP')
                    ->whereNotNull('spec_code')
                    ->value('spec_code');
                $expectedSpecCode = trim((string) ($lockedHeader->product_type_code ?: $firstSpecCode ?: ''));
                if ($expectedSpecCode !== '' && strcasecmp($expectedSpecCode, $payload['spec_code']) !== 0) {
                    throw ValidationException::withMessages(['raw_scan_code' => 'ชนิดสินค้าของ WIP ต้องตรงกับ WIP รายการแรกของ Job']);
                }
                if ($expectedSpecCode === '') {
                    $lockedHeader->update([
                        'product_type_code' => $payload['product_type_code'] ?: $payload['spec_code'],
                        'product_type_name' => $payload['spec_name'],
                        'sku_code' => $payload['sku_code'],
                    ]);
                }
            }
            $sourceKey = strtoupper(trim($payload['source_type'])).':'.strtoupper(trim($payload['source_id']));

            if (CutJobLine::on($connection)->where('cut_job_id', $lockedJob->id)->where('source_key', $sourceKey)->exists()) {
                throw ValidationException::withMessages(['raw_scan_code' => 'รายการ WIP/FG นี้ถูกสแกนใน Job นี้แล้ว']);
            }

            $lineNo = ((int) CutJobLine::on($connection)
                ->where('cut_job_header_id', $lockedHeader->id)
                ->lockForUpdate()
                ->max('line_no')) + 1;

            $line = CutJobLine::on($connection)->create([
                ...$payload,
                'cut_job_id' => $lockedJob->id,
                'cut_job_header_id' => $lockedHeader->id,
                'line_no' => $lineNo,
                'source_key' => $sourceKey,
                'qc_status' => 'passed',
                'receive_status' => 'pending',
                'print_status' => 'pending',
                'wms_status' => 'pending',
                'scanned_by' => $userId,
                'scanned_at' => now(),
            ]);

            $lockedJob->update(['status' => 'scanning', 'started_at' => $lockedJob->started_at ?? now()]);
            $lockedHeader->update(['status' => 'scanning']);

            return $line->refresh();
        });
    }
}
