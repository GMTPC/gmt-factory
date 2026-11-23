<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CutJobHeader extends Model
{
    use HasFactory;

    protected $connection = 'bplus';

    protected $fillable = [
        'cut_job_id',
        'header_no',
        'group_code',
        'sku_code',
        'product_type_code',
        'product_type_name',
        'lot_no',
        'lot_date',
        'target_qty',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'lot_date' => 'date',
            'target_qty' => 'decimal:3',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(CutJob::class, 'cut_job_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(CutJobLine::class);
    }
}
