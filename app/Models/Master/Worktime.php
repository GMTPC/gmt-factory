<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Worktime extends Model
{
    protected $connection = 'legacy';

    protected $table = 'wip_worktime';

    protected $primaryKey = 'wwt_id';

    protected $fillable = ['wwt_index', 'wwt_status', 'wwt_line', 'wwt_date'];
}
