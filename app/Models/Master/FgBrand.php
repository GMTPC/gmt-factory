<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class FgBrand extends Model
{
    protected $connection = 'legacy';

    protected $table = 'brands';

    protected $primaryKey = 'brd_id';

    protected $guarded = [];
}
