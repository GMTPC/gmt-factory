<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Model;

class CsvCheck extends Model
{
    protected $connection = 'legacy';

    protected $table = 'check_csv_wh';

    protected $primaryKey = 'ccw_id';

    protected $guarded = [];
}
