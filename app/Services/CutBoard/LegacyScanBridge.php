<?php

namespace App\Services\CutBoard;

use App\Models\CutJob;
use App\Models\CutJobLine;
use App\Models\Master\FgBrand;
use App\Models\Warehouse\CsvCheck;
use App\Models\Warehouse\CsvCheckIndex;
use Illuminate\Support\Facades\DB;

final class LegacyScanBridge
{
    public function record(CutJob $job, CutJobLine $line): array
    {
        return DB::connection(config('cutboard.source_connection'))->transaction(function () use ($job, $line): array {
            // WMS receipt API joins check_csv_wh.ccw_barcode to the view's interface_qr.
            // Keep the original scanner text in cut_job_lines.raw_scan_code.
            $legacyBarcode = $line->interface_qr ?: $line->raw_scan_code;
            $indexRow = $job->legacy_scan_index
                ? CsvCheckIndex::query()->where('cswi_index', $job->legacy_scan_index)->lockForUpdate()->first()
                : null;

            if (! $indexRow) {
                $nextIndex = ((int) CsvCheckIndex::query()->lock('WITH (UPDLOCK, HOLDLOCK)')->max('cswi_index')) + 1;
                $indexRow = CsvCheckIndex::query()->create([
                    'cswi_index' => $nextIndex,
                    // The legacy column is decimal(…,2) and the old WMS flow uses .01.
                    // The new job number is kept in cut_jobs; the local job stores this index.
                    'cswi_ziptape' => config('cutboard.legacy_ziptape', '0.01'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $csv = CsvCheck::query()->where('ccw_barcode', $legacyBarcode)->first();
            if (! $csv) {
                $csv = CsvCheck::query()->create([
                    'ccw_index' => $indexRow->cswi_index,
                    'ccw_barcode' => $legacyBarcode,
                    'ccw_lot' => $line->lot_no,
                    'ccw_amount' => (int) round((float) $line->qty),
                    'ccw_out' => 0,
                    'ccw_type_c' => $line->source_type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $snapshot = $line->spec_snapshot ?? [];
            $brandId = $snapshot['legacy_brand_id'] ?? null;
            $brandUpdated = false;
            $brandPreviousStatus = null;
            if ($brandId !== null) {
                $brand = FgBrand::query()->whereKey($brandId)->lockForUpdate()->first();
                $brandPreviousStatus = $brand?->brd_status;
                $brandUpdated = $brand?->update(['brd_status' => 2]) ?? false;
            }

            return [
                'index' => (int) $indexRow->cswi_index,
                'cswi_id' => (int) $indexRow->cswi_id,
                'ccw_id' => (int) $csv->ccw_id,
                'brand_updated' => $brandUpdated,
                'brand_previous_status' => $brandPreviousStatus,
            ];
        });
    }
}
