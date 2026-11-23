<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MasterFace extends Model
{
    protected $table = 'master_faces';
    protected $fillable = ['code', 'name', 'is_default'];
    protected $casts = ['is_default' => 'boolean'];
}
