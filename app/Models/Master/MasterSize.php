<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MasterSize extends Model
{
    protected $table = 'master_sizes';
    protected $fillable = ['code', 'label', 'width_mm', 'length_mm'];
}
