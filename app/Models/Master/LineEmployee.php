<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LineEmployee extends Model
{
    protected $connection = 'legacy';

    protected $table = 'user_employee';

    protected $primaryKey = 'ue_id';

    protected $fillable = ['ue_empno', 'ue_name', 'ue_line', 'ue_remark', 'ue_status'];

    public function firstEmployeeGroups(): HasMany
    {
        return $this->hasMany(EmployeeGroup::class, 'eg_emp_id_1', 'ue_id');
    }

    public function secondEmployeeGroups(): HasMany
    {
        return $this->hasMany(EmployeeGroup::class, 'eg_emp_id_2', 'ue_id');
    }
}
