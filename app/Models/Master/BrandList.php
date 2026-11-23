<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class BrandList extends Model
{
    protected $connection = 'legacy';

    protected $table = 'brandlist';

    protected $primaryKey = 'bl_id';

    public $timestamps = false;
}
