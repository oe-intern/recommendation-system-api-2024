<?php

namespace App\DTO\Payload;

class ShopProductRecommendationRequestDTO
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
     * Type scores between type of products.
     *
     * @var array
     */
    public array $type_scores;

    /**
     * Total number of orders.
     *
     * @var int
     */
    public int $total;

    /**
     * Product scores between products.
     *
     * @var array
     */
    public array $product_scores;

    /**
     * ProductRecommendationRequestDTO constructor.
     *
     * @param int $number_of_items
     * @param array $products
     * @param array $type_scores
     * @param int $total
     * @param array $product_scores
     */
    public function __construct(
        int $number_of_items,
        array $products,
        array $type_scores,
        int $total,
        array $product_scores,
    ) {
        $this->number_of_items = $number_of_items;
        $this->products = $products;
        $this->type_scores = $type_scores;
        $this->total = $total;
        $this->product_scores = $product_scores;
    }

    /**
     * Convert DTO to array for API request.
     */
    public function toArray(): array
    {
        return [
            'number_of_items' => $this->number_of_items,
            'products' => $this->products,
            'type_scores' => $this->type_scores,
            'total' => $this->total,
            'product_scores' => $this->product_scores,
        ];
    }
}
