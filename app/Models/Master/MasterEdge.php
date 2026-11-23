<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MasterEdge extends Model
{
    protected $table = 'master_edges';
    protected $fillable = ['code', 'name'];
}
