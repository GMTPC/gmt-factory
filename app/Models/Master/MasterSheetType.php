<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MasterSheetType extends Model
{
    protected $table = 'master_sheet_types';
    protected $fillable = ['code', 'name'];
}
