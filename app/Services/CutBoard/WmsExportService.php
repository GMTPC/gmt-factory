<?php

namespace App\Services\CutBoard;

use App\Models\CutJob;
use App\Models\CutJobLine;
use App\Models\CutJobWmsExport;
use Illuminate\Support\Str;

final class WmsExportService
{
    public function __construct(private readonly LegacyScanBridge $legacyBridge) {}

    public function queue(CutJob $job, CutJobLine $line): CutJobWmsExport
    {
        $export = CutJobWmsExport::query()->firstOrCreate(
            ['cut_job_line_id' => $line->id],
            [
                'cut_job_id' => $job->id,
                'batch_code' => $job->job_no,
                'barcode' => $line->interface_qr ?: ($line->raw_scan_code ?: $line->source_id),
                'lot_no' => $line->lot_no,
                'quantity' => $line->qty,
                'source_type' => $line->source_type,
                'sync_status' => config('cutboard.legacy_bridge') ? 'pending' : 'local_only',
            ],
        );

        if ($export->sync_status === 'synced' || $export->sync_status === 'local_only') {
            return $export;
        }

        if (! config('cutboard.legacy_bridge')) {
            $line->update(['wms_status' => 'local_only']);

            return $export;
        }

        try {
            $legacy = $this->legacyBridge->record($job, $line);
            $export->update([
                'sync_status' => 'synced',
                'sync_error' => null,
                'legacy_csv_index' => $legacy['index'],
                'legacy_csv_index_id' => $legacy['cswi_id'],
                'legacy_csv_row_id' => $legacy['ccw_id'],
                'synced_at' => now(),
            ]);
            $line->update([
                'legacy_ccw_id' => $legacy['ccw_id'],
                'legacy_scan_index' => $legacy['index'],
                'legacy_cswi_id' => $legacy['cswi_id'],
                'wms_status' => 'queued',
                'spec_snapshot' => array_merge($line->spec_snapshot ?? [], array_filter([
                    'legacy_brand_previous_status' => $line->source_type === 'WIP' ? $legacy['brand_previous_status'] : null,
                ], static fn ($value) => $value !== null)),
            ]);
            $job->update([
                'legacy_scan_index' => $legacy['index'],
                'legacy_cswi_id' => $legacy['cswi_id'],
            ]);
        } catch (\Throwable $exception) {
            $export->update([
                'sync_status' => 'failed',
                'sync_error' => Str::limit($exception->getMessage(), 2000),
            ]);
            $line->update(['wms_status' => 'bridge_failed']);
        }

        return $export->refresh();
    }
}
