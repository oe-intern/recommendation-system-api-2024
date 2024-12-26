<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartEventRequest extends FormRequest
{
    /**
     * Handle an incoming request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'number_of_items' => 'sometimes|nullable|integer',
            'product_id' => ['required', 'regex:/^\d+$/'],
            'data' => 'sometimes|nullable',
        ];
    }
}
