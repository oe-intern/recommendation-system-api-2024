<?php

namespace App\DTO\Response;

use App\Collections\ProductCollection;
use Illuminate\Contracts\Support\Arrayable;

readonly class SetManualRecommendationResponse implements Arrayable
{
    /**
     * SetManualRecommendationResponse constructor.
     *
     * @param ProductCollection $product
     * @param array $recommendations
     */
    public function __construct(
        private ProductCollection $product,
        private array $recommendations,
    ) {}

    /**
     * Convert data to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->product->getGid(),
            'recommended_ids' => $this->recommendations,
            'recommendations' => $this->product->getRecommendationType(),
        ];
    }
}
