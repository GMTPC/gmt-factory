<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finishing_log_inputs')) {
            // Which received WIP tags (and how many sheets of each) went into one output tag.
            // One output can use several input tags, and one input tag can be split over several outputs.
            Schema::create('finishing_log_inputs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('production_log_id')->constrained('finishing_production_logs');
                $table->foreignId('receipt_id')->constrained('finishing_wip_receipts');
                $table->string('scan_code', 255);
                $table->decimal('quantity', 15, 3);
                $table->timestamps();

                $table->index('receipt_id');
                $table->index('production_log_id');
            });
        }

        // Existing data: one scanned tag -> one output, the whole tag quantity used.
        if (DB::table('finishing_log_inputs')->doesntExist() && Schema::hasColumn('finishing_wip_receipts', 'production_log_id')) {
            $now = now();
            DB::table('finishing_wip_receipts as r')
                ->join('finishing_production_logs as l', 'l.id', '=', 'r.production_log_id')
                ->where('l.status', 'confirmed')->whereNull('l.deleted_at')
                ->orderBy('r.id')
                ->get(['r.id', 'r.scan_code', 'r.quantity', 'r.production_log_id', 'l.quantity_in'])
                ->chunk(200)
                ->each(fn ($rows) => DB::table('finishing_log_inputs')->insert($rows->map(fn ($row) => [
                    'production_log_id' => $row->production_log_id,
                    'receipt_id' => $row->id,
                    'scan_code' => $row->scan_code,
                    'quantity' => min((float) $row->quantity, (float) $row->quantity_in),
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->values()->all()));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('finishing_log_inputs');
    }
};
