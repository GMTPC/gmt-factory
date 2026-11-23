<?php

namespace App\Http\Requests\CutBoard;

use App\Enums\ProductionLine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCutJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'production_line' => ['required', Rule::enum(ProductionLine::class)],
            'work_date' => ['required', 'date'],
        ];
    }
}
