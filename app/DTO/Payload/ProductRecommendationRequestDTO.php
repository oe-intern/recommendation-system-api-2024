<?php

namespace App\DTO\Payload;

class ProductRecommendationRequestDTO
{
    /**
     * Number of items to recommend.
     *
     * @var int
     */
    public int $numberOfItems;

    /**
     * List of products to recommend of a shop.
     *
     * @var array
     */
    public array $products;

    /**
     * Product ID.
     *
     * @var string
     */
    public string $productId;

    /**
     * ProductRecommendationRequestDTO constructor.
     *
     * @param int $numberOfItems
     * @param array $products
     * @param string $productId
     */
    public function __construct(int $numberOfItems, array $products, string $productId)
    {
        $this->numberOfItems = $numberOfItems;
        $this->products = $products;
        $this->productId = $productId;
    }

    /**
     * Convert DTO to array for API request.
     */
    public function toArray(): array
    {
        return [
            'number_of_items' => $this->numberOfItems,
            'products' => $this->products,
            'product_id' => $this->productId,
        ];
    }
}
