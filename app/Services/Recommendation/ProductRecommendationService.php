<?php

namespace App\Services\Recommendation;

use App\Collections\ProductCollection;
use App\Contracts\Commands\IProductCommand;
use App\Contracts\Commands\IShopSettingCommand;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\Exceptions\ProductNotFoundException;
use App\Lib\Utils;
use App\Objects\Enums\RecommendationState;
use App\Objects\Enums\RecommendationType;

class ProductRecommendationService implements IProductRecommendation
{
    /**
     * @var IProductQuery
     */
    protected IProductQuery $productQuery;

    /**
     * @var IShopQuery
     */
    protected IShopQuery $shopQuery;

    /**
     * @var IProductCommand
     */
    protected IProductCommand $productCommand;

    /**
     * @var IProductQueryShopify
     */
    protected IProductQueryShopify $productService;

    /**
     * @var IShopSettingCommand
     */
    protected IShopSettingCommand $shopSettingCommand;

    /**
     * ProductRecommendationService constructor.
     *
     * @param IProductQuery $productQuery
     * @param IShopQuery $shopQuery
     * @param IProductCommand $productCommand
     * @param IProductQueryShopify $productService
     * @param IShopSettingCommand $shopSettingCommand
     */
    public function __construct(
        IProductQuery $productQuery,
        IShopQuery $shopQuery,
        IProductCommand $productCommand,
        IProductQueryShopify $productService,
        IShopSettingCommand $shopSettingCommand,
    ) {
        $this->productQuery = $productQuery;
        $this->shopQuery = $shopQuery;
        $this->productCommand = $productCommand;
        $this->productService = $productService;
        $this->shopSettingCommand = $shopSettingCommand;
    }

    /**
     * Get list of recommended products for a product.
     *
     * @param string $shopId
     * @param string $productId
     * @return array
     */
    public function getRecommendedProducts(string $shopId, string $productId): array
    {
        $product = $this->productQuery->getByShopIdAndId($shopId, $productId);
        $recommendationType = $product->getRecommendationType();

        $productIds = match ($recommendationType) {
            RecommendationType::AUTO => $this->getAutoRecommendation($shopId, $productId),
            RecommendationType::MANUAL => $this->getManualRecommendation($shopId, $productId),
            default => $this->getDefaultRecommendation($shopId, $productId),
        };

        return $this->productQuery->getHandleAndGidByIds($productIds);
    }

    /**
     * Get list of also viewed products for a product from system recommendation.
     *
     * @param string $shopId
     * @param string $productId
     * @return array
     */
    private function getAutoRecommendation(string $shopId, string $productId): array
    {
        $productIds = $this->productQuery->getAutoRecommendationProducts($productId);

        $activeProducts = $this->productQuery->getActiveProducts($productIds);
        $numberOfItems = $this->shopQuery->getById($shopId)->settings()->get()->getNumberOfItems();

        return array_slice($activeProducts, 0, $numberOfItems);
    }

    /**
     * Get list of also viewed products for a product from manual recommendation.
     *
     * @param string $shopId
     * @param string $productId
     * @return array
     */
    private function getManualRecommendation(string $shopId, string $productId): array
    {
        $productIds = $this->productQuery->getManualProducts($productId);
        return $this->productQuery->getActiveProducts($productIds);
    }

    /**
     * Get list of manual product gid for a product.
     *
     * @param string $productId
     * @return array
     */
    public function getManualProducts(string $productId): array
    {
        $productIds = $this->productQuery->getManualProducts($productId);
        return $this->productQuery->getListGidByIds($productIds);
    }

    /**
     * Get list of also viewed products for a product from relationship product.
     *
     * @param string $shopId
     * @param string $productId
     * @return array
     */
    private function getDefaultRecommendation(string $shopId, string $productId): array
    {
        $product = $this->productQuery->getById($productId);
        $defaultIds = $product->getDefaultRecommendationIds();

        $activeProducts = $this->productQuery->getActiveProducts($defaultIds);
        $numberOfItems = $this->shopQuery->getById($shopId)->settings()->get()->getNumberOfItems();

        return collect($activeProducts)->random(min(count($activeProducts), $numberOfItems))->toArray();
    }

    /**
     * Set list of recommended products for a product.
     *
     * @param string $shopId
     * @param string $productId
     * @param array $recommendedGids
     * @param string|null $recommendationType
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendedProducts(
        string $shopId,
        string $productId,
        array $recommendedGids,
        ?string $recommendationType,
    ): ProductCollection {
        $recommendationType = RecommendationType::tryFrom($recommendationType);
        $recommendedIds = $this->productQuery->validateListProductGid($shopId, $recommendedGids);
        $product = $this->productQuery->getById($productId);
        $recommendedIds = array_unique(array_filter($recommendedIds, fn($id) => (string)$id !== $productId));

        $this->productCommand->setManualProduct(
            $product,
            $shopId,
            $recommendedIds,
            $recommendationType ?? $product->getRecommendationType(),
        );

        return $product;
    }

    /**
     * Set the recommendation type for a product.
     *
     * @param string $shopId
     * @param string $productId
     * @param string $recommendationType
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendationType(
        string $shopId,
        string $productId,
        string $recommendationType,
    ): ProductCollection {
        $this->productQuery->validateProductId($shopId, $productId);

        $recommendationType = RecommendationType::tryFrom($recommendationType);
        $product = $this->productQuery->getById($productId);

        $this->productCommand->setRecommendationType($product, $recommendationType);

        return $product;
    }

    /**
     * Get settings for auto recommendation.
     *
     * @param string $shopId
     * @return array
     */
    public function getShopSettings(string $shopId): array
    {
        $shop = $this->shopQuery->getById($shopId);
        return $this->shopQuery->getShopSettings($shop);
    }

    /**
     * Set auto recommendation for a shop.
     *
     * @param string $shopId
     * @param array $settings
     *
     * @return array
     */
    public function setShopSettings(
        string $shopId,
        array $settings,
    ): array {
        $shop = $this->shopQuery->getById($shopId);
        return $this->shopSettingCommand->setShopSettings($shop, $settings);
    }

    /**
     * Activate recommendation for all product of a shop.
     *
     * @param string $shopId
     * @param string $status
     * @return bool
     */
    public function activateRecommendation(
        string $shopId,
        string $status,
    ): bool {
        $status = RecommendationState::from($status);
        if ($status === $this->getShopRecommendationState($shopId)) {
            return true;
        }

        match ($status) {
            RecommendationState::ACTIVE => $this->activate($shopId),
            RecommendationState::INACTIVE => $this->deactivate($shopId),
        };

        return true;
    }

    /**
     * Activate recommendation for all product of a shop.
     *
     * @param string $shopId
     * @return void
     */
    private function activate(string $shopId): void
    {
        $this->settingShop($shopId, RecommendationState::ACTIVE);
        $this->productCommand->activateRecommendation($shopId);
    }

    /**
     * Deactivate recommendation for all product of a shop.
     *
     * @param string $shopId
     * @return void
     */
    private function deactivate(string $shopId): void
    {
        $this->settingShop($shopId, RecommendationState::INACTIVE);
        $this->productCommand->deactivateRecommendation($shopId);
    }

    /**
     * Set recommendation state for a shop.
     *
     * @param string $shopId
     * @param RecommendationState $status
     * @return void
     */
    private function settingShop(string $shopId, RecommendationState $status): void
    {
        $this->shopSettingCommand->setRecommendationState($shopId, $status);
    }

    /**
     * Get shop recommendation state.
     *
     * @param string $shopId
     * @return RecommendationState
     */
    private function getShopRecommendationState(string $shopId): RecommendationState
    {
        return $this->shopQuery->getById($shopId)
            ->settings()->get()->getAutoRecommendation();
    }

    /**
     * Update product default recommendation for a shop.
     *
     * @param array $recommendationData
     * @param array $gidToIdMap
     * @return bool
     */
    public function updateManyDefaultRecommendation(array $recommendationData, array $gidToIdMap): bool
    {
        return $this->productCommand->updateManyDefaultRecommendation(
            $this->matchData($recommendationData, $gidToIdMap, 'default_recommendation_ids'),
        );
    }

    /**
     * Update product recommendation for a shop.
     *
     * @param array $recommendationData
     * @param array $gidToIdMap
     * @return bool
     */
    public function updateManyRecommendation(array $recommendationData, array $gidToIdMap): bool
    {
        return $this->productCommand->updateManyRecommendation(
            $this->matchData($recommendationData, $gidToIdMap, 'recommendation_ids'),
        );
    }

    /**
     * Format Shopify product ID
     *
     * @param string $id
     * @return string
     */
    private function formatShopifyId(string $id): string
    {
        if (str_starts_with($id, 'gid://')) {
            return $id;
        }
        return "gid://shopify/Product/$id";
    }

    /**
     * Match recommendation data with product data.
     *
     * @param array $recommendationData
     * @param array $productData
     * @param string $attribute
     * @return array
     */
    private function matchData(array $recommendationData, array $productData, string $attribute): array
    {
        $result = [];
        foreach ($productData as $gid => $id) {
            $formattedGid = $this->formatShopifyId($gid);
            if (!isset($recommendationData[$formattedGid])) {
                continue;
            }

            $recommendationGids = $recommendationData[$formattedGid];
            $recommendations = array_map(fn($item) => $productData[Utils::getIdFromGid($item)], $recommendationGids);

            $result[$id] = $recommendations;
        }

        return $result;
    }
}
