<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcProcessTran extends Model
{
    protected $connection = 'legacy';

    protected $table = 'qc_process_trans';

    protected $guarded = [];

    public function process(): BelongsTo
    {
        return $this->belongsTo(QcProcessGoods::class, 'qc_process_id');
    }
}
