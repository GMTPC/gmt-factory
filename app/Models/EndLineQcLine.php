<?php

namespace App\Models;

use App\Models\Master\MasterBrand;
use App\Models\Master\MasterProductSpec;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EndLineQcLine extends Model
{
    protected $fillable = ['header_id', 'running_no', 'line_no', 'spec_id', 'product_type', 'product_state', 'brand', 'brand_id', 'weight', 'time_at', 'qty', 'unit', 'status', 'remark', 'product_group_id', 'lot', 'sku_code', 'full_name', 'sku_name', 'tag', 'full_tag', 'wip_code', 'wip_status_code', 'wip_status_name', 'tag_no', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['weight' => 'decimal:2', 'qty' => 'integer'];
    }

    public function header(): BelongsTo { return $this->belongsTo(EndLineQcHeader::class, 'header_id'); }
    public function spec(): BelongsTo { return $this->belongsTo(MasterProductSpec::class, 'spec_id'); }
    public function brandMaster(): BelongsTo { return $this->belongsTo(MasterBrand::class, 'brand_id'); }
}
