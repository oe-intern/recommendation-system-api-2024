<?php

namespace App\Jobs;

use App\Contracts\Commands\IProductCommand;
use App\Contracts\ModelRecommendation\IRecommendationApi;
use App\Contracts\Objects\Transform\ShopifyTransform;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\DTO\Payload\ProductRecommendationRequestDTO;
use App\Objects\Transform\ProductTransform;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateRecommendationProduct implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * @var int
     */
    private const MAX_RECOMMENDATION_PRODUCTS = 6;

    /**
     * @var int
     */
    private const MAX_RETRY_PROCESS = 3;

    /**
     * @var int
     */
    private const DELAY_RECOMMEND_PROCESS = 60;

    /**
     * @var string
     */
    protected string $product_id; // 1 minute

    /**
     * @var IProductQueryShopify
     */
    private IProductQueryShopify $product_query_shopify;

    /**
     * @var IRecommendationApi
     */
    private IRecommendationApi $recommendation_api_service;

    /**
     * @var IProductCommand
     */
    private IProductCommand $product_command;

    /**
     * @var IProductQuery
     */
    private IProductQuery $product_query;

    /**
     * @var ShopifyTransform
     */
    private ShopifyTransform $product_transform;

    /**
     * UpdateRecommendationProduct constructor.
     */
    public function __construct(string $product_id)
    {
        $this->product_id = $product_id;
    }

    /**
     * Handle the job
     *
     * @param IProductQueryShopify $product_query_shopify
     * @param IRecommendationApi $recommendation_api_service
     * @param IProductCommand $product_command
     * @param IProductQuery $product_query
     * @param ProductTransform $product_transform
     * @return void
     */
    public function handle(
        IProductQueryShopify $product_query_shopify,
        IRecommendationApi $recommendation_api_service,
        IProductCommand $product_command,
        IProductQuery $product_query,
        ProductTransform $product_transform,
    ): void {
        $this->initializeServices(
            $product_query_shopify,
            $recommendation_api_service,
            $product_command,
            $product_query,
            $product_transform,
        );

        $request_data = $this->getRequestData();
        $recommendations = $this->getRecommendations($request_data);

        $product_command->updateProductRecommendation($this->product_id, $recommendations);
    }

    /**
     * Initialize services
     *
     * @param IProductQueryShopify $product_query_shopify
     * @param IRecommendationApi $recommendation_api_service
     * @param IProductCommand $product_command
     * @param IProductQuery $product_query
     * @param ProductTransform $product_transform
     * @return void
     */
    private function initializeServices(
        IProductQueryShopify $product_query_shopify,
        IRecommendationApi $recommendation_api_service,
        IProductCommand $product_command,
        IProductQuery $product_query,
        ProductTransform $product_transform,
    ): void {
        $this->product_query_shopify = $product_query_shopify;
        $this->recommendation_api_service = $recommendation_api_service;
        $this->product_command = $product_command;
        $this->product_query = $product_query;
        $this->product_transform = $product_transform;
    }

    /**
     * Get request data for recommendation API
     *
     * @return ProductRecommendationRequestDTO
     */
    private function getRequestData(): ProductRecommendationRequestDTO
    {
        $products = $this->product_query_shopify->fetchAll();
        $products_data = $this->product_transform->shopifyDataListToModelApiListData($products);

        return new ProductRecommendationRequestDTO(
            self::MAX_RECOMMENDATION_PRODUCTS,
            $products_data,
            $this->product_id,
        );
    }

    /**
     * Get recommendations for a product
     *
     * @param ProductRecommendationRequestDTO $request_data
     * @return array
     */
    private function getRecommendations(ProductRecommendationRequestDTO $request_data): array
    {
        $recommendations_response = $this->retryFetchingRecommendations($request_data);
        return $this->product_query->getIdsByGids($recommendations_response);
    }

    /**
     * Retry fetching recommendations
     *
     * @param ProductRecommendationRequestDTO $request_data
     * @return array
     */
    private function retryFetchingRecommendations(
        ProductRecommendationRequestDTO $request_data,
    ): array {
        $retry = 0;
        do {
            try {
                return $this->recommendation_api_service->recommendProduct($request_data);
            } catch (Exception $e) {
                sleep(self::DELAY_RECOMMEND_PROCESS);
            }
        } while ($retry < self::MAX_RETRY_PROCESS);

        return [];
    }

}
