<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MasterThickness extends Model
{
    protected $table = 'master_thicknesses';
    protected $fillable = ['code', 'mm', 'label'];
    protected $casts = ['mm' => 'float'];
}
