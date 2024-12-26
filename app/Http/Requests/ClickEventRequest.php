<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClickEventRequest extends FormRequest
{
    /**
     * Handle an incoming request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'regex:/^\d+$/'],
            'data' => 'sometimes|nullable',
        ];
    }
}
