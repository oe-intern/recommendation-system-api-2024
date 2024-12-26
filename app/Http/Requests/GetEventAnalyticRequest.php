<?php

namespace App\Http\Requests;

use App\Objects\Enums\AnalyticGroupBy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class GetEventAnalyticRequest extends FormRequest
{
    /**
     * Handle an incoming request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'group_by' => [
                'sometimes',
                new Enum(AnalyticGroupBy::class),
            ],
            'product_id' => 'sometimes|nullable|string',
            'start_date' => 'required|date_format:Y-m-d|before_or_equal:end_date',
            'end_date' => 'required|date_format:Y-m-d|before_or_equal:today',
        ];
    }
}
