<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class ProductTypeEmployee extends Model
{
    protected $connection = 'legacy';

    protected $table = 'producttype_emp';

    protected $primaryKey = 'pe_id';
}
