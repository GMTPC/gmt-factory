<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class MasterProductGroup extends Model
{
    protected $table = 'master_product_groups';

    protected $fillable = ['code', 'name'];
}
