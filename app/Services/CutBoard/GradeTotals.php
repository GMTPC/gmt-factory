<?php

namespace App\Services\CutBoard;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;

class GradeTotals
{
    public function forJobs(ConnectionInterface $db, array $jobIds): Collection
    {
        if ($jobIds === []) {
            return collect();
        }

        // Legacy typeofproduct(): type 2 is grade B. brandlist code 09 is
        // grade C and takes precedence over the product type.
        $gradeC = config('cutboard.grades.c_brand_code', '09');
        $gradeB = config('cutboard.grades.b_product_type', '2');
        $typePosition = (int) config('cutboard.grades.product_type_position', 2);
        $latestTypes = $db->table('producttype_emp')->whereIn('pe_working_id', $jobIds)
            ->select('pe_working_id')->selectRaw('MAX(pe_id) AS pe_id')->groupBy('pe_working_id');

        return $db->table('brands as f')
            ->leftJoin('brandlist as bl', 'bl.bl_id', '=', 'f.brd_brandlist_id')
            ->leftJoinSub($latestTypes, 'latest', 'latest.pe_working_id', '=', 'f.brd_working_id')
            ->leftJoin('producttype_emp as p', 'p.pe_id', '=', 'latest.pe_id')
            ->whereIn('f.brd_working_id', $jobIds)
            ->select('f.brd_working_id')
            ->selectRaw('SUM(CASE WHEN COALESCE(bl.bl_code, ?) <> ? AND (SUBSTRING(p.pe_type_code, ?, 1) <> ? OR p.pe_type_code IS NULL) THEN f.brd_amount ELSE 0 END) AS grade_a', ['', $gradeC, $typePosition, $gradeB])
            ->selectRaw('SUM(CASE WHEN COALESCE(bl.bl_code, ?) <> ? AND SUBSTRING(p.pe_type_code, ?, 1) = ? THEN f.brd_amount ELSE 0 END) AS grade_b', ['', $gradeC, $typePosition, $gradeB])
            ->selectRaw('SUM(CASE WHEN bl.bl_code = ? THEN f.brd_amount ELSE 0 END) AS grade_c', [$gradeC])
            ->groupBy('f.brd_working_id')->get()->keyBy('brd_working_id');
    }
}
