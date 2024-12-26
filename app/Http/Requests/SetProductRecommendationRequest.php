<?php

namespace App\Http\Requests;

use App\Objects\Enums\RecommendationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SetProductRecommendationRequest extends FormRequest
{
    /**
     * Handle an incoming request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'recommended_ids' => 'required|array',
            'recommended_ids.*' => 'string',
            'recommendation_type' => [
                'sometimes',
                'nullable',
                'string',
                new Enum(RecommendationType::class),
            ],
        ];
    }
}
