<?php

namespace App\Http\Requests;

use App\Objects\Enums\RecommendationState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SetActiveRecommendationRequest extends FormRequest
{
    /**
     * Handle an incoming request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                new Enum(RecommendationState::class),
            ],
        ];
    }
}
