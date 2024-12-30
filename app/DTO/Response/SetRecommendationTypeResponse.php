<?php

namespace App\DTO\Response;

use App\Collections\ProductCollection;
use Illuminate\Contracts\Support\Arrayable;

readonly class SetRecommendationTypeResponse implements Arrayable
{
    /**
     * SetRecommendationTypeResponse constructor.
     *
     * @param ProductCollection $product
     */
    public function __construct(
        private ProductCollection $product,
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
            'recommendation_type' => $this->product->getRecommendationType(),
        ];
    }
}
