<?php

namespace App\Http\Requests\CutBoard;

use Illuminate\Foundation\Http\FormRequest;

class RejectCutJobLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:1000']];
    }
}
