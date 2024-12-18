<?php

namespace App\DTO\Payload;

class ProductRecommendationRequestDTO
{
    /**
     * Number of items to recommend.
     *
     * @var int
     */
    public int $number_of_items;

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
    public string $product_id;

    /**
     * ProductRecommendationRequestDTO constructor.
     *
     * @param int $number_of_items
     * @param array $products
     * @param string $product_id
     */
    public function __construct(int $number_of_items, array $products, string $product_id)
    {
        $this->number_of_items = $number_of_items;
        $this->products = $products;
        $this->product_id = $product_id;
    }

    /**
     * Convert DTO to array for API request.
     */
    public function toArray(): array
    {
        return [
            'number_of_items' => $this->number_of_items,
            'products' => $this->products,
            'product_id' => $this->product_id,
        ];
    }
}
