<?php

namespace App\Http\Requests\CutBoard;

use Illuminate\Foundation\Http\FormRequest;

class CreateCutJobHeaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'header_no' => ['required', 'string', 'max:40'],
            'group_code' => ['nullable', 'string', 'max:100'],
            'sku_code' => ['nullable', 'string', 'max:100'],
            'product_type_code' => ['nullable', 'string', 'max:100'],
            'product_type_name' => ['nullable', 'string', 'max:255'],
            'lot_no' => ['nullable', 'string', 'max:100'],
            'lot_date' => ['nullable', 'date'],
            'target_qty' => ['required', 'numeric', 'min:0'],
        ];
    }
}
