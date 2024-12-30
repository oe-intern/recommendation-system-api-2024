<?php

namespace App\DTO\Response;

use App\Collections\ProductCollection;
use Illuminate\Contracts\Support\Arrayable;

readonly class GetProductRecommendationTypeResponse implements Arrayable
{
    /**
     * GetProductRecommendationTypeResponse constructor.
     *
     * @param ProductCollection $products
     */
    public function __construct(
        private ProductCollection $products,
    ) {}

    /**
     * Convert data to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->products->getGid(),
            'recommendation_type' => $this->products->getRecommendationType(),
        ];
    }
}
