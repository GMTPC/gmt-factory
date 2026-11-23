<?php

namespace App\Http\Resources\CutBoard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CutJobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_no' => $this->job_no,
            'production_line' => $this->production_line,
            'work_date' => $this->work_date?->toDateString(),
            'status' => $this->status,
            'headers_count' => $this->headers_count ?? $this->whenCounted('headers'),
            'lines_count' => $this->lines_count ?? $this->whenCounted('lines'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
