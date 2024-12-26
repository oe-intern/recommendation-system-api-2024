<?php

namespace App\DTO\Request;

use App\Objects\Enums\RecommendationState;
use Illuminate\Foundation\Http\FormRequest;

class SetActiveRecommendationRequestDTO extends BaseRequestDTO
{
    /**
     * ActiveRecommendationRequestDTO constructor.
     *
     * @param RecommendationState $status
     */
    public function __construct(
        public RecommendationState $status,
    ) {}

    /**
     * Convert data from request to DTO.
     *
     * @param FormRequest $data
     * @return self
     */
    public static function fromRequest(FormRequest $data): self
    {
        return new self(
            RecommendationState::from($data['status']),
        );
    }
}
