<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class WipWorking extends Model
{
    protected $connection = 'legacy';

    protected $table = 'wip_working';

    protected $primaryKey = 'ww_id';
}
