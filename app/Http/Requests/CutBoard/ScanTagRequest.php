<?php

namespace App\Http\Requests\CutBoard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScanTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'job_id' => ['required', 'integer'],
            'header_id' => ['required', 'integer'],
            'raw_scan_code' => ['required', 'string', 'max:500'],
            'source_type' => ['required', Rule::in(['WIP', 'FG'])],
            'employee_group_id' => ['required_if:source_type,WIP', 'nullable', 'integer', 'min:1'],
            'brand_code' => ['nullable', 'string', 'max:50'],
            'product_type_code' => ['nullable', 'string', 'max:100'],
            'source_id' => ['nullable', 'string', 'max:100'],
            'wip_code' => ['nullable', 'string', 'max:150'],
            'fg_code' => ['nullable', 'string', 'max:150'],
            'spec_code' => ['nullable', 'string', 'max:150'],
            'spec_name' => ['nullable', 'string', 'max:255'],
            'spec_snapshot' => ['nullable', 'array'],
            'sku_code' => ['nullable', 'string', 'max:100'],
            'lot_no' => ['nullable', 'string', 'max:100'],
            'qty' => ['nullable', 'numeric', 'min:0.001'],
        ];
    }
}
