<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Model;

class WmsLocation extends Model
{
    protected $connection = 'legacy';

    protected $table = 'api_wms_wmsc';

    public $timestamps = false;
}
