<?php

namespace Modules\Finishing\App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Finishing\App\Models\ProductionLog;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV of confirmed Finishing output for import into B+.
 * One row per issued WIP tag. Column set is a first draft: adjust COLUMNS / row() once the
 * B+ import layout is confirmed. Encoding is CP874 like the existing Grade C B+ export.
 */
class BplusExportService
{
    /** key => header written on the first line */
    public const COLUMNS = [
        'doc_date' => 'DOC_DATE',
        'shift' => 'SHIFT',
        'station' => 'STATION',
        'job_no' => 'JOB_NO',
        'product_code' => 'PRODUCT_CODE',
        'product_name' => 'PRODUCT_NAME',
        'lot' => 'LOT',
        'tag_code' => 'TAG_CODE',
        'source_tag' => 'SOURCE_TAG',
        'qty_in' => 'QTY_IN',
        'qty_out' => 'QTY_OUT',
        'defect' => 'DEFECT_QTY',
        'unit' => 'UNIT',
        'weight_kg' => 'WEIGHT_KG',
        'sheet_type' => 'SHEET_TYPE',
        'machine' => 'MACHINE',
        'inspector' => 'INSPECTOR',
        'recorder' => 'RECORDER',
        'recorded_at' => 'RECORDED_AT',
    ];

    /** Confirmed logs (with job + station) in export order. */
    public function logs(callable $scope): Collection
    {
        $query = ProductionLog::query()->with(['job.recorder', 'station:id,code', 'user:id,name'])
            ->where('status', 'confirmed')->whereNotNull('output_tag_code');
        $scope($query);

        return $query->orderBy('station_id')->orderBy('job_id')->orderBy('id')->get();
    }

    public function download(Collection $logs, string $fileName): StreamedResponse
    {
        $tz = config('finishing.timezone', 'Asia/Bangkok');
        $scanCodes = DB::table('finishing_wip_receipts')->whereIn('production_log_id', $logs->pluck('id'))->pluck('scan_code', 'production_log_id');
        $rows = $logs->map(fn (ProductionLog $log) => $this->row($log, $scanCodes->get($log->id), $tz));

        return response()->streamDownload(function () use ($rows): void {
            $file = fopen('php://output', 'wb');
            $this->put($file, array_values(self::COLUMNS));
            foreach ($rows as $row) {
                $this->put($file, array_map(fn (string $key) => $row[$key] ?? '', array_keys(self::COLUMNS)));
            }
            fclose($file);
        }, $fileName, ['Content-Type' => 'text/csv; charset=cp874']);
    }

    private function row(ProductionLog $log, ?string $scanCode, string $tz): array
    {
        $job = $log->job;
        $number = fn ($value) => $value === null ? '' : rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');

        return [
            'doc_date' => $log->shift_date?->format('d/m/Y'),
            'shift' => $log->shift === 'night' ? 'B' : 'A',
            'station' => $log->station?->code,
            'job_no' => $job?->job_no,
            'product_code' => $log->product_type_code ?: $job?->product_type_code,
            'product_name' => $log->product_type_name ?: $job?->product_type_name,
            'lot' => $log->output_lot,
            'tag_code' => $log->output_tag_code,
            'source_tag' => $scanCode,
            'qty_in' => $number($log->quantity_in),
            'qty_out' => $number($log->quantity_out),
            'defect' => $number($log->defect_count),
            'unit' => 'แผ่น',
            'weight_kg' => $log->weight_kg === null ? '' : number_format((float) $log->weight_kg, 2, '.', ''),
            'sheet_type' => $job?->pvc_type,
            'machine' => $job?->machine_name,
            'inspector' => $job?->inspector_name,
            'recorder' => $job?->recorder?->name ?? $log->user?->name,
            'recorded_at' => $log->created_at?->copy()->timezone($tz)->format('d/m/Y H:i'),
        ];
    }

    /** @param resource $file */
    private function put($file, array $values): void
    {
        fputcsv($file, array_map(fn ($value) => iconv('UTF-8', 'CP874//TRANSLIT', (string) $value), $values), ',', '"', '');
    }
}
