<?php

namespace App\Models\Production;

use App\Models\Master\BrandList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QcProcessGoods extends Model
{
    protected $connection = 'legacy';

    protected $table = 'qc_process_goods';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'qc_date' => 'date',
            'amount' => 'integer',
            'loss_amount' => 'integer',
        ];
    }

    public function brandList(): BelongsTo
    {
        return $this->belongsTo(BrandList::class, 'brand', 'bl_code');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(QcProcessTran::class, 'qc_process_id');
    }
}
