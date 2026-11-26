<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'bplus';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('cut_job_wms_exports')) {
            return;
        }

        Schema::connection($this->connection)->create('cut_job_wms_exports', function (Blueprint $table): void {
            $table->id();
            // cut_job_line_id owns the cascading relation; cut_job_id is indexed to avoid
            // a second SQL Server cascade path through cut_job_lines.
            $table->unsignedBigInteger('cut_job_id');
            $table->foreignId('cut_job_line_id')->constrained('cut_job_lines')->cascadeOnDelete();
            $table->unique('cut_job_line_id');
            $table->string('batch_code', 40);
            $table->string('barcode', 500);
            $table->string('lot_no', 100)->nullable();
            $table->decimal('quantity', 18, 3);
            $table->string('source_type', 10);
            $table->string('sync_status', 20)->default('pending');
            $table->text('sync_error')->nullable();
            $table->unsignedBigInteger('legacy_csv_index')->nullable();
            $table->unsignedBigInteger('legacy_csv_index_id')->nullable();
            $table->unsignedBigInteger('legacy_csv_row_id')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['cut_job_id', 'sync_status']);
            $table->index(['legacy_csv_index', 'sync_status']);
        });

        DB::connection($this->connection)->table('cut_job_lines')
            ->join('cut_jobs', 'cut_jobs.id', '=', 'cut_job_lines.cut_job_id')
            ->where(function ($query): void {
                $query->whereNotNull('cut_job_lines.legacy_ccw_id')
                    ->orWhereNotNull('cut_job_lines.legacy_scan_index');
            })
            ->select([
                'cut_job_lines.id',
                'cut_job_lines.cut_job_id',
                'cut_jobs.job_no',
                'cut_job_lines.interface_qr',
                'cut_job_lines.raw_scan_code',
                'cut_job_lines.source_id',
                'cut_job_lines.lot_no',
                'cut_job_lines.qty',
                'cut_job_lines.source_type',
                'cut_job_lines.wms_status',
                'cut_job_lines.legacy_scan_index',
                'cut_job_lines.legacy_cswi_id',
                'cut_job_lines.legacy_ccw_id',
                'cut_job_lines.created_at',
                'cut_job_lines.updated_at',
            ])
            ->orderBy('cut_job_lines.id')
            ->chunkById(500, function ($lines): void {
                foreach ($lines as $line) {
                    DB::connection($this->connection)->table('cut_job_wms_exports')->insert([
                        'cut_job_id' => $line->cut_job_id,
                        'cut_job_line_id' => $line->id,
                        'batch_code' => $line->job_no,
                        'barcode' => $line->interface_qr ?: ($line->raw_scan_code ?: $line->source_id),
                        'lot_no' => $line->lot_no,
                        'quantity' => $line->qty,
                        'source_type' => $line->source_type,
                        'sync_status' => $line->legacy_ccw_id ? 'synced' : 'pending',
                        'legacy_csv_index' => $line->legacy_scan_index,
                        'legacy_csv_index_id' => $line->legacy_cswi_id,
                        'legacy_csv_row_id' => $line->legacy_ccw_id,
                        'synced_at' => $line->legacy_ccw_id ? $line->updated_at : null,
                        'created_at' => $line->created_at,
                        'updated_at' => $line->updated_at,
                    ]);
                }
            }, 'cut_job_lines.id');
    }

    public function down(): void
    {
        // Existing export records may have been imported before this migration was recorded.
    }
};
