<?php

namespace App\Models;

use App\Models\Master\MasterBrand;
use App\Models\Master\MasterEdge;
use App\Models\Master\MasterFace;
use App\Models\Master\MasterProductSpec;
use App\Models\Master\MasterSheetType;
use App\Models\Master\MasterSize;
use App\Models\Master\MasterThickness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EndLineQcHeader extends Model
{
    protected $fillable = ['line', 'qc_date', 'shift', 'qc_inspector', 'status', 'lot', 'running_no', 'job_no', 'brand_id', 'sheet_type_id', 'edge_id', 'size_id', 'thickness_id', 'face_id', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['qc_date' => 'date'];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(EndLineQcLine::class, 'header_id');
    }

    public function brand() { return $this->belongsTo(MasterBrand::class); }
    public function sheetType() { return $this->belongsTo(MasterSheetType::class, 'sheet_type_id'); }
    public function edge() { return $this->belongsTo(MasterEdge::class); }
    public function size() { return $this->belongsTo(MasterSize::class); }
    public function thickness() { return $this->belongsTo(MasterThickness::class); }
    public function face() { return $this->belongsTo(MasterFace::class); }
}
