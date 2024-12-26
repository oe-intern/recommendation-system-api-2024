<?php

namespace App\DTO\Request;

use App\Objects\Enums\RecommendationType;
use Illuminate\Foundation\Http\FormRequest;

class SetRecommendationTypeRequestDTO extends BaseRequestDTO
{
    /**
     * SetRecommendationTypeRequestDTO constructor.
     *
     * @param string $productId
     * @param RecommendationType $recommendationType
     */
    public function __construct(
        public string $productId,
        public RecommendationType $recommendationType,
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
            $data['product_id'],
            RecommendationType::from($data['recommendation_type']),
        );
    }
}
