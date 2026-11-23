<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MasterProductSpec extends Model
{
    protected $table = 'master_product_specs';

    protected $fillable = [
        'code', 'name', 'grade', 'brand', 'description', 'full_desc', 'status',
        'sheet_type', 'edge_type', 'width_mm', 'length_mm', 'thickness_mm', 'category',
    ];
}
