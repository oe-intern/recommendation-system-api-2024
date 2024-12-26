<?php

namespace App\DTO\Request;

use App\Objects\Enums\RecommendationType;
use Illuminate\Foundation\Http\FormRequest;

class SetProductRecommendationRequestDTO extends BaseRequestDTO
{
    /**
     * SetProductRecommendationRequestDTO constructor.
     *
     * @param array $recommendedIds
     * @param RecommendationType|null $recommendationType
     */
    public function __construct(
        public array $recommendedIds,
        public ?RecommendationType $recommendationType,
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
            $data['recommended_ids'],
            isset($data['recommendation_type']) ? RecommendationType::from($data['recommendation_type']) : null,
        );
    }
}
