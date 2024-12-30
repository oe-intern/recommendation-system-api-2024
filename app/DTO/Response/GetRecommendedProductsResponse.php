<?php

namespace App\DTO\Response;

use Illuminate\Contracts\Support\Arrayable;

readonly class GetRecommendedProductsResponse implements Arrayable
{
    /**
     * GetRecommendedProductsResponse constructor.
     *
     * @param array $recommendedProducts
     */
    public function __construct(
        private array $recommendedProducts,
    ) {}

    /**
     * Convert data to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->recommendedProducts;
    }
}
