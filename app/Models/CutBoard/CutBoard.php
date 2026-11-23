<?php

namespace App\Models\CutBoard;

use Illuminate\Database\Eloquent\Model;

class CutBoard extends Model
{
    protected $connection = 'legacy';

    protected $table = 'wms_qc_cut_boards';

    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope('min_year', function ($query): void {
            $query->whereYear('created_at', '>=', config('cutboard.min_year'));
        });
    }
}
