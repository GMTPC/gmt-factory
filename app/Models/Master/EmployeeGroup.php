<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeGroup extends Model
{
    protected $connection = 'legacy';

    protected $table = 'emp_group';

    protected $primaryKey = 'eg_id';

    protected $fillable = ['eg_emp_id_1', 'eg_emp_id_2', 'eg_line', 'eg_division', 'eg_status'];

    public function employeeOne(): BelongsTo
    {
        return $this->belongsTo(LineEmployee::class, 'eg_emp_id_1', 'ue_id');
    }

    public function employeeTwo(): BelongsTo
    {
        return $this->belongsTo(LineEmployee::class, 'eg_emp_id_2', 'ue_id');
    }
}
