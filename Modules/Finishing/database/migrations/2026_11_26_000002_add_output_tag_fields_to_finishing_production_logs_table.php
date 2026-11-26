<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = ['product_type_code', 'product_type_name', 'weight_kg', 'output_lot', 'output_tag_code'];

    public function up(): void
    {
        if (Schema::hasColumn('finishing_production_logs', 'output_tag_code')) {
            return;
        }

        Schema::table('finishing_production_logs', function (Blueprint $table): void {
            $table->string('product_type_code', 50)->nullable();
            $table->string('product_type_name', 255)->nullable();
            $table->decimal('weight_kg', 12, 3)->nullable();
            // Finishing output lot: yymmdd + shift letter (A = day, B = night) + 3-digit sequence.
            $table->string('output_lot', 20)->nullable()->index();
            $table->string('output_tag_code', 150)->nullable()->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('finishing_production_logs', 'output_tag_code')) {
            return;
        }

        Schema::table('finishing_production_logs', function (Blueprint $table): void {
            $table->dropIndex(['output_lot']);
            $table->dropIndex(['output_tag_code']);
            $table->dropColumn($this->columns);
        });
    }
};
