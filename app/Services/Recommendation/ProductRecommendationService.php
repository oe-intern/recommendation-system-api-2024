<?php

namespace App\Services\Recommendation;

use App\Collections\ProductCollection;
use App\Contracts\Commands\IProductCommand;
use App\Contracts\Commands\IShopSettingCommand;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\DTO\Request\SetActiveRecommendationRequestDTO;
use App\DTO\Request\SetProductRecommendationRequestDTO;
use App\DTO\Request\SetRecommendationTypeRequestDTO;
use App\DTO\Request\UpdateShopSettingRequestDTO;
use App\Exceptions\ProductNotFoundException;
use App\Lib\Utils;
use App\Objects\Enums\RecommendationState;
use App\Objects\Enums\RecommendationType;
use App\Objects\Enums\ShopifyType;

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
            RecommendationType::MANUAL => $this->getManualRecommendation($productId),
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
     * @param string $productId
     * @return array
     */
    private function getManualRecommendation(string $productId): array
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
     * @param SetProductRecommendationRequestDTO $requestDTO
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendedProducts(
        string $shopId,
        string $productId,
        SetProductRecommendationRequestDTO $requestDTO,
    ): ProductCollection {
        $recommendedIds = $this->productQuery->validateListProductGid(
            $shopId, $requestDTO->recommendedIds,
        );
        $product = $this->productQuery->getById($productId);
        $recommendedIds = array_unique(array_filter($recommendedIds, fn($id) => (string)$id !== $productId));

        $this->productCommand->setManualProduct(
            $product,
            $shopId,
            $recommendedIds,
            $requestDTO->recommendationType ?? $product->getRecommendationType(),
        );

        return $product;
    }

    /**
     * Set the recommendation type for a product.
     *
     * @param string $shopId
     * @param string $productId
     * @param SetRecommendationTypeRequestDTO $requestDTO
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendationType(
        string $shopId,
        string $productId,
        SetRecommendationTypeRequestDTO $requestDTO,
    ): ProductCollection {
        $this->productQuery->validateProductId($shopId, $productId);

        $product = $this->productQuery->getById($productId);

        $this->productCommand->setRecommendationType($product, $requestDTO->recommendationType);

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
     * @param UpdateShopSettingRequestDTO $requestDTO
     *
     * @return array
     */
    public function setShopSettings(
        string $shopId,
        UpdateShopSettingRequestDTO $requestDTO,
    ): array {
        $shop = $this->shopQuery->getById($shopId);
        $settings = [
            'number_of_items' => $requestDTO->numberOfItems,
            'layout' => $requestDTO->layout,
            'background_color' => $requestDTO->backgroundColor,
            'text_color' => $requestDTO->textColor,
        ];

        return $this->shopSettingCommand->setShopSettings($shop, $settings);
    }

    /**
     * Activate recommendation for all product of a shop.
     *
     * @param string $shopId
     * @param SetActiveRecommendationRequestDTO $requestDTO
     * @return bool
     */
    public function activateRecommendation(
        string $shopId,
        SetActiveRecommendationRequestDTO $requestDTO,
    ): bool {
        $status = $requestDTO->status;
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
     * Get shop recommendation state.
     *
     * @param string $shopId
     * @return RecommendationState
     */
    private function getShopRecommendationState(string $shopId): RecommendationState
    {
        return $this->shopQuery
            ->getById($shopId)
            ->settings()->get()->getAutoRecommendation();
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
            $formattedGid = Utils::addPrefixGraphId($gid, ShopifyType::PRODUCT);

            if (!isset($recommendationData[$formattedGid])) {
                continue;
            }

            $recommendationGids = $recommendationData[$formattedGid];
            $result[$id] = $this->mapRecommendationGidsToProducts($recommendationGids, $productData);
        }

        return $result;
    }

    /**
     * Map recommendation GIDs to product IDs
     *
     * @param array $recommendationGids
     * @param array $productData
     * @return array
     */
    private function mapRecommendationGidsToProducts(array $recommendationGids, array $productData): array
    {
        return array_map(function ($item) use ($productData) {
            return $productData[Utils::getIdFromGid($item)] ?? null;
        }, $recommendationGids);
    }
}
