<?php

namespace App\Http\Requests;

use App\Objects\Enums\RecommendationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SetRecommendationTypeRequest extends FormRequest
{
    /**
     * Handle an incoming request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'product_id' => 'string',
            'recommendation_type' => [
                'required',
                new Enum(RecommendationType::class),
            ],
        ];
    }
}
