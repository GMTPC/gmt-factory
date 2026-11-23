<?php

namespace App\Http\Requests\CutBoard;

use Illuminate\Foundation\Http\FormRequest;

class EditCutJobLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'qty' => ['required', 'integer', 'min:1'],
            'employee_group_id' => ['required', 'integer', 'min:1'],
            'brand_id' => ['nullable', 'integer', 'min:1'],
            'checker' => ['nullable', 'string', 'max:100'],
            'backboard_no' => ['nullable', 'string', 'max:100'],
            'remark' => ['nullable', 'string', 'max:500'],
        ];
    }
}
