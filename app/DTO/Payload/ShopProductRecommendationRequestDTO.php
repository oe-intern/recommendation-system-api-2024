<?php

namespace App\DTO\Payload;

class ShopProductRecommendationRequestDTO
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
     * Type scores between type of products.
     *
     * @var array
     */
    public array $typeScores;

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
    public array $productScores;

    /**
     * ProductRecommendationRequestDTO constructor.
     *
     * @param int $numberOfItems
     * @param array $products
     * @param array $typeScores
     * @param int $total
     * @param array $productScores
     */
    public function __construct(
        int $numberOfItems,
        array $products,
        array $typeScores,
        int $total,
        array $productScores,
    ) {
        $this->numberOfItems = $numberOfItems;
        $this->products = $products;
        $this->typeScores = $typeScores;
        $this->total = $total;
        $this->productScores = $productScores;
    }

    /**
     * Convert DTO to array for API request.
     */
    public function toArray(): array
    {
        return [
            'number_of_items' => $this->numberOfItems,
            'products' => $this->products,
            'type_scores' => $this->typeScores,
            'total' => $this->total,
            'product_scores' => $this->productScores,
        ];
    }
}
