<?php

namespace Tests\Feature\CutBoard;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StartLineJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'cutboard.source_connection' => 'cutboard-layout-test',
            'database.connections.cutboard-layout-test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        $db = DB::connection('cutboard-layout-test');
        $schema = $db->getSchemaBuilder();
        $schema->create('wip_worktime', function (Blueprint $table): void {
            $table->id('wwt_id');
            $table->string('wwt_line');
        });
        $schema->create('wip_working', function (Blueprint $table): void {
            $table->id('ww_id');
            $table->string('ww_group');
            $table->string('ww_line');
            $table->integer('ww_wwt_index');
            $table->string('ww_division');
            $table->string('ww_status');
            $table->dateTime('ww_start_date');
            $table->date('ww_lot_date');
            $table->timestamps();
        });
        $schema->create('producttype_emp', function (Blueprint $table): void {
            $table->id('pe_id');
            $table->integer('pe_working_id')->nullable();
            $table->integer('pe_index')->nullable();
            $table->string('pe_type_code');
            $table->string('pe_type_name');
            $table->timestamps();
        });
        $db->table('producttype_emp')->insert([
            'pe_type_code' => 'A10109', 'pe_type_name' => 'ขอบลาด ธรรมดา 1.2 x 2.4 m. x9 mm.',
        ]);
        $this->actingAs((new User)->forceFill(['id' => 1, 'name' => 'Test operator', 'username' => 'test']));
    }

    public function test_selected_product_is_saved_with_the_new_job(): void
    {
        $this->from('/wms/cut-boards/lines/L1')->post('/wms/cut-boards/lines/L1/jobs', [
            'group_code' => 'B', 'work_date' => '2026-11-05', 'product_code' => 'A10109',
        ])->assertRedirect('/wms/cut-boards/lines/L1')->assertSessionHasNoErrors();

        $db = DB::connection('cutboard-layout-test');
        $job = $db->table('wip_working')->first();
        $this->assertSame('L1', $job->ww_line);
        $product = $db->table('producttype_emp')->where('pe_working_id', $job->ww_id)->first();
        $this->assertSame('A10109', $product->pe_type_code);
        $this->assertSame('ขอบลาด ธรรมดา 1.2 x 2.4 m. x9 mm.', $product->pe_type_name);
    }

    public function test_unknown_product_is_rejected_without_creating_a_job(): void
    {
        $this->from('/wms/cut-boards/lines/L1')->post('/wms/cut-boards/lines/L1/jobs', [
            'group_code' => 'B', 'work_date' => '2026-11-05', 'product_code' => 'ZZZZZZ',
        ])->assertSessionHasErrors('product_code');

        $this->assertSame(0, DB::connection('cutboard-layout-test')->table('wip_working')->count());
    }

    public function test_product_can_still_be_inferred_from_the_first_wip_scan(): void
    {
        $this->from('/wms/cut-boards/lines/L1')->post('/wms/cut-boards/lines/L1/jobs', [
            'group_code' => 'B', 'work_date' => '2026-11-05', 'product_code' => '',
        ])->assertSessionHasNoErrors();

        $db = DB::connection('cutboard-layout-test');
        $job = $db->table('wip_working')->first();
        $this->assertNotNull($job);
        $this->assertFalse($db->table('producttype_emp')->where('pe_working_id', $job->ww_id)->exists());
    }
}
