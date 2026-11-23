<?php

namespace Tests\Unit\CutBoard;

use App\Services\CutBoard\GradeTotals;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use PDO;
use Tests\TestCase;

class GradeTotalsTest extends TestCase
{
    public function test_latest_product_type_and_grade_c_precedence_preserve_fg_quantity(): void
    {
        $db = new SQLiteConnection(new PDO('sqlite::memory:'));
        $schema = $db->getSchemaBuilder();
        $schema->create('brands', function (Blueprint $table): void {
            $table->integer('brd_working_id');
            $table->integer('brd_brandlist_id');
            $table->integer('brd_amount');
        });
        $schema->create('brandlist', function (Blueprint $table): void {
            $table->integer('bl_id');
            $table->string('bl_code');
        });
        $schema->create('producttype_emp', function (Blueprint $table): void {
            $table->integer('pe_id');
            $table->integer('pe_working_id');
            $table->string('pe_type_code');
        });
        $db->table('brandlist')->insert([
            ['bl_id' => 1, 'bl_code' => '04'], ['bl_id' => 7, 'bl_code' => '09'],
        ]);
        $db->table('producttype_emp')->insert([
            ['pe_id' => 1, 'pe_working_id' => 10, 'pe_type_code' => 'A10109'],
            ['pe_id' => 2, 'pe_working_id' => 11, 'pe_type_code' => 'A10109'],
            ['pe_id' => 3, 'pe_working_id' => 11, 'pe_type_code' => 'A20109'],
        ]);
        $db->table('brands')->insert([
            ['brd_working_id' => 10, 'brd_brandlist_id' => 1, 'brd_amount' => 100],
            ['brd_working_id' => 10, 'brd_brandlist_id' => 7, 'brd_amount' => 20],
            ['brd_working_id' => 11, 'brd_brandlist_id' => 1, 'brd_amount' => 75],
            ['brd_working_id' => 11, 'brd_brandlist_id' => 7, 'brd_amount' => 10],
            ['brd_working_id' => 12, 'brd_brandlist_id' => 1, 'brd_amount' => 999],
        ]);

        $totals = (new GradeTotals)->forJobs($db, [10, 11]);

        $this->assertCount(2, $totals);
        $this->assertEquals([100, 0, 20], [$totals[10]->grade_a, $totals[10]->grade_b, $totals[10]->grade_c]);
        $this->assertEquals([0, 75, 10], [$totals[11]->grade_a, $totals[11]->grade_b, $totals[11]->grade_c]);
        foreach ([10, 11] as $jobId) {
            $grades = $totals[$jobId];
            $this->assertEquals(
                $db->table('brands')->where('brd_working_id', $jobId)->sum('brd_amount'),
                $grades->grade_a + $grades->grade_b + $grades->grade_c,
            );
        }
    }

    public function test_empty_job_list_does_not_query_legacy_tables(): void
    {
        $db = new SQLiteConnection(new PDO('sqlite::memory:'));
        $this->assertTrue((new GradeTotals)->forJobs($db, [])->isEmpty());
    }
}
