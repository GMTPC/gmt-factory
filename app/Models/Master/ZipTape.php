<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class ZipTape extends Model
{
    protected $connection = 'legacy';

    protected $table = 'wip_ziptape';

    protected $primaryKey = 'wz_id';

    protected $fillable = ['wz_line', 'wz_worktime_id', 'wz_amount'];
}
