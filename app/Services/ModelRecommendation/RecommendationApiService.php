<?php

namespace App\Services\ModelRecommendation;

use App\Contracts\ModelRecommendation\IRecommendationApi;
use Exception;
use Illuminate\Support\Facades\Http;

class RecommendationApiService implements IRecommendationApi
{
    protected string $base_url;

    public function __construct()
    {
        $this->base_url = config('services.recommendation_api.base_url');
    }

    /**
     * Call the external recommend endpoint.
     *
     * @param int $total_order
     * @param array $type_scores
     * @param array $product_scores
     * @param array $products
     * @return array
     *
     * @throws Exception
     */
    public function recommend(int $total_order, array $type_scores, array $product_scores, array $products): array
    {
        $response = Http::post("{$this->base_url}/recommend", [
            'total' => $total_order,
            'type_scores' => $type_scores,
            'product_scores' => $product_scores,
            'products' => $products,
        ]);

        // handle ...

    }

    /**
     * Call the external pre-recommend endpoint.
     *
     * @param int $total_order
     * @param array $type_scores
     * @param array $product_scores
     * @param array $products
     * @return array
     *
     * @throws Exception
     */
    public function preRecommend(int $total_order, array $type_scores, array $product_scores, array $products): array
    {
        $response = Http::post("{$this->base_url}/prerecommend", [
            'total' => $total_order,
            'type_scores' => $type_scores,
            'product_scores' => $product_scores,
            'products' => $products,
        ]);

        // handle ...
    }
}
