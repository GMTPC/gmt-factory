<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class WasteDetail extends Model
{
    protected $connection = 'legacy';

    protected $table = 'wip_waste_detail';

    protected $primaryKey = 'wwd_id';

    protected $fillable = ['wwd_line', 'wwd_index', 'wwd_barcode', 'wwd_lot', 'wwd_amount', 'wwd_date'];
}
