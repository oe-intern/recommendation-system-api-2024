<?php

namespace App\Jobs;

use App\Contracts\Commands\IProductCommand;
use App\Contracts\ModelRecommendation\IRecommendationApi;
use App\Contracts\Objects\Transform\ShopifyTransform;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\DTO\Payload\ProductRecommendationRequestDTO;
use App\Objects\Enums\RecommendationType;
use App\Objects\Transform\ProductTransform;
use App\Models\User;
use App\Services\Shopify\UserContext;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

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
    private const DELAY_RECOMMEND_PROCESS = 10;

    /**
     * @var string
     */
    protected string $productId; // 1 minute

    /**
     * @var string
     */
    protected string $shopDomain;

    /**
     * @var IProductQueryShopify
     */
    private IProductQueryShopify $productQueryShopify;

    /**
     * @var IRecommendationApi
     */
    private IRecommendationApi $recommendationApiService;

    /**
     * @var IProductCommand
     */
    private IProductCommand $productCommand;

    /**
     * @var IProductQuery
     */
    private IProductQuery $productQuery;

    /**
     * @var IShopQuery
     */
    private IShopQuery $shopQuery;

    /**
     * @var ShopifyTransform
     */
    private ShopifyTransform $productTransform;

    /**
     * UpdateRecommendationProduct constructor.
     */
    public function __construct(string $productId, string $shopDomain)
    {
        $this->productId = $productId;
        $this->shopDomain = $shopDomain;
    }

    /**
     * Handle the job
     *
     * @param IProductQueryShopify $productQueryShopify
     * @param IRecommendationApi $recommendationApiService
     * @param IProductCommand $productCommand
     * @param IProductQuery $productQuery
     * @param IShopQuery $shopQuery
     * @param ProductTransform $productTransform
     * @return void
     */
    public function handle(
        IProductQueryShopify $productQueryShopify,
        IRecommendationApi $recommendationApiService,
        IProductCommand $productCommand,
        IProductQuery $productQuery,
        IShopQuery $shopQuery,
        ProductTransform $productTransform,
    ): void {
        $this->initializeServices(
            $productQueryShopify,
            $recommendationApiService,
            $productCommand,
            $productQuery,
            $shopQuery,
            $productTransform,
        );
        Log::info('Update recommendation for product ' . $this->productId);
        $requestData = $this->getRequestData();
        $recommendations = $this->getRecommendations($requestData);
        Log::info('Recommendations for product ' . $this->productId . ' ' . json_encode($recommendations));
        $this->updateRecommendationProduct($this->productId, $recommendations);
    }

    /**
     * Initialize services
     *
     * @param IProductQueryShopify $productQueryShopify
     * @param IRecommendationApi $recommendationApiService
     * @param IProductCommand $productCommand
     * @param IProductQuery $productQuery
     * @param IShopQuery $shopQuery
     * @param ProductTransform $productTransform
     * @return void
     */
    private function initializeServices(
        IProductQueryShopify $productQueryShopify,
        IRecommendationApi $recommendationApiService,
        IProductCommand $productCommand,
        IProductQuery $productQuery,
        IShopQuery $shopQuery,
        ProductTransform $productTransform,
    ): void {
        $this->productQueryShopify = $productQueryShopify;
        $this->recommendationApiService = $recommendationApiService;
        $this->productCommand = $productCommand;
        $this->productQuery = $productQuery;
        $this->shopQuery = $shopQuery;
        $this->productTransform = $productTransform;

        $this->setContext();
    }

    /**
     * Set user context
     */
    private function setContext(): void
    {
        $userContext = app(UserContext::class);
        $shopSession = User::query()->where('name', $this->shopDomain)->first();
        $userContext->setUser($shopSession);
    }

    /**
     * Get request data for recommendation API
     *
     * @return ProductRecommendationRequestDTO
     */
    private function getRequestData(): ProductRecommendationRequestDTO
    {
        $products = $this->productQueryShopify->fetchAll();
        $productsData = $this->productTransform->shopifyDataListToModelApiListData($products);

        return new ProductRecommendationRequestDTO(
            self::MAX_RECOMMENDATION_PRODUCTS,
            $productsData,
            $this->productId,
        );
    }

    /**
     * Get recommendations for a product
     *
     * @param ProductRecommendationRequestDTO $requestData
     * @return array
     */
    private function getRecommendations(ProductRecommendationRequestDTO $requestData): array
    {
        $recommendationsResponse = $this->retryFetchingRecommendations($requestData);
        return $this->productQuery->getIdsByGids($recommendationsResponse);
    }

    /**
     * Retry fetching recommendations
     *
     * @param ProductRecommendationRequestDTO $requestData
     * @return array
     */
    private function retryFetchingRecommendations(
        ProductRecommendationRequestDTO $requestData,
    ): array {
        $retry = 0;
        do {
            try {
                return $this->recommendationApiService->recommendProduct($requestData);
            } catch (Exception $e) {
                sleep(self::DELAY_RECOMMEND_PROCESS);
            }
        } while ($retry < self::MAX_RETRY_PROCESS);

        return [];
    }


    /**
     * Update recommendation for a product
     *
     * @param string $productId
     * @param array $recommendations
     * @return void
     */
    private function updateRecommendationProduct(string $productId, array $recommendations): void
    {
        $recommendationType = $this->getRecommendationType();
        $this->productCommand->updateNewProductRecommendation($productId, $recommendations, $recommendationType);
    }

    /**
     * Get recommendation type of shop for a product
     *
     * @return RecommendationType
     */
    private function getRecommendationType(): RecommendationType
    {
        $shop = $this->shopQuery->getByDomain($this->shopDomain);
        return $shop->settings()->get()
            ->isAutoRecommendationActive() ? RecommendationType::AUTO : RecommendationType::DEFAULT;
    }

}
