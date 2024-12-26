<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopSettingRequest extends FormRequest
{
    /**
     * Handle an incoming request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'number_of_items' => 'required|integer|min:1|max:6',
            'layout' => 'required|string',
            'background_color' => 'required|string',
            'text_color' => 'required|string',
        ];
    }
}
