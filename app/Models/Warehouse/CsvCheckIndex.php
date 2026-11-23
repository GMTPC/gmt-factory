<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Model;

class CsvCheckIndex extends Model
{
    protected $connection = 'legacy';

    protected $table = 'check_csv_wh_index';

    protected $primaryKey = 'cswi_id';

    protected $guarded = [];
}
