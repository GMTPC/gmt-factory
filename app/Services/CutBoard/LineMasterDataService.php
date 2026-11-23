<?php

namespace App\Services\CutBoard;

use App\Models\Master\EmployeeGroup;
use App\Models\Master\LineEmployee;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class LineMasterDataService
{
    public function employees(string $line, bool $activeOnly = false)
    {
        return LineEmployee::query()->where('ue_line', $line)
            ->when($activeOnly, fn ($query) => $query->where('ue_status', 1))
            ->orderByDesc('ue_status')->orderBy('ue_name')->get()
            ->map(fn (LineEmployee $employee) => (object) [
                'id' => $employee->ue_id,
                'name' => $employee->ue_name,
                'remark' => $employee->ue_remark,
                'status' => $employee->ue_status,
            ]);
    }

    public function groups(string $line, bool $activeOnly = false)
    {
        return EmployeeGroup::query()->with(['employeeOne', 'employeeTwo'])
            ->where('eg_line', $line)
            ->when($activeOnly, fn ($query) => $query->where('eg_status', 1))
            ->orderBy('eg_id')->get()
            ->map(fn (EmployeeGroup $group) => (object) [
                'id' => $group->eg_id,
                'status' => $group->eg_status,
                'division' => $group->eg_division,
                'employee1' => $group->employeeOne?->ue_name,
                'employee2' => $group->employeeTwo?->ue_name,
            ]);
    }

    public function activeGroup(string $line, int $groupId): ?EmployeeGroup
    {
        return EmployeeGroup::query()->with(['employeeOne', 'employeeTwo'])
            ->where('eg_line', $line)->where('eg_status', 1)->find($groupId);
    }

    public function createEmployees(string $line, array $rows): void
    {
        DB::connection('legacy')->transaction(function () use ($line, $rows): void {
            $number = (int) LineEmployee::query()->lockForUpdate()->count();
            foreach ($rows as $row) {
                $number++;
                LineEmployee::query()->create([
                    'ue_empno' => 'EM'.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                    'ue_name' => $row['name'],
                    'ue_line' => $line,
                    'ue_remark' => $row['remark'] ?? null,
                    'ue_status' => 1,
                ]);
            }
        });
    }

    public function deactivateEmployee(string $line, int $employeeId): void
    {
        $updated = LineEmployee::query()->where('ue_id', $employeeId)->where('ue_line', $line)
            ->update(['ue_status' => 0, 'updated_at' => now()]);
        abort_unless($updated, 404);
    }

    public function createGroups(string $line, array $rows): void
    {
        DB::connection('legacy')->transaction(function () use ($line, $rows): void {
            $activeIds = LineEmployee::query()->where('ue_line', $line)->where('ue_status', 1)->lockForUpdate()
                ->pluck('ue_id')->map(fn ($id) => (int) $id)->all();
            foreach ($rows as $row) {
                $first = (int) $row['employee1'];
                $second = (int) $row['employee2'];
                if ($first === $second || ! in_array($first, $activeIds, true) || ! in_array($second, $activeIds, true)) {
                    throw ValidationException::withMessages(['groups' => 'เลือกพนักงานที่ยังใช้งานอยู่ในไลน์นี้ 2 คนที่ไม่ซ้ำกัน']);
                }

                EmployeeGroup::query()->create([
                    'eg_emp_id_1' => $first,
                    'eg_emp_id_2' => $second,
                    'eg_line' => $line,
                    'eg_division' => $row['division'],
                    'eg_status' => 1,
                ]);
            }
        });
    }

    public function setGroupStatus(string $line, int $groupId, bool $active): void
    {
        $updated = EmployeeGroup::query()->where('eg_id', $groupId)->where('eg_line', $line)
            ->update(['eg_status' => $active ? 1 : 0, 'updated_at' => now()]);
        abort_unless($updated, 404);
    }
}
