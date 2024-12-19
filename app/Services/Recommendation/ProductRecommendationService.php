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
    protected IProductQuery $product_query;

    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * @var IProductCommand
     */
    protected IProductCommand $product_command;

    /**
     * @var IProductQueryShopify
     */
    protected IProductQueryShopify $product_service;

    /**
     * @var IShopSettingCommand
     */
    protected IShopSettingCommand $shop_setting_command;

    /**
     * ProductRecommendationService constructor.
     *
     * @param IProductQuery $product_query
     * @param IShopQuery $shop_query
     * @param IProductCommand $product_command
     * @param IProductQueryShopify $product_service
     * @param IShopSettingCommand $shop_setting_command
     */
    public function __construct(
        IProductQuery $product_query,
        IShopQuery $shop_query,
        IProductCommand $product_command,
        IProductQueryShopify $product_service,
        IShopSettingCommand $shop_setting_command,
    ) {
        $this->product_query = $product_query;
        $this->shop_query = $shop_query;
        $this->product_command = $product_command;
        $this->product_service = $product_service;
        $this->shop_setting_command = $shop_setting_command;
    }

    /**
     * Get list of recommended products for a product.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return array
     */
    public function getRecommendedProducts(string $shop_id, string $product_id): array
    {
        $product = $this->product_query->getByShopIdAndId($shop_id, $product_id);
        $recommendation_type = $product->getRecommendationType();

        $product_ids = match ($recommendation_type) {
            RecommendationType::AUTO => $this->getAutoRecommendation($shop_id, $product_id),
            RecommendationType::MANUAL => $this->getManualRecommendation($shop_id, $product_id),
            default => $this->getDefaultRecommendation($shop_id, $product_id),
        };

        return $this->product_query->getHandleAndGidByIds($product_ids);
    }

    /**
     * Get list of also viewed products for a product from system recommendation.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return array
     */
    private function getAutoRecommendation(string $shop_id, string $product_id): array
    {
        $list_product_ids = $this->product_query->getAutoRecommendationProducts($product_id);

        $active_products = $this->product_query->getActiveProducts($list_product_ids);
        $number_of_items = $this->shop_query->getById($shop_id)->settings()->get()->getNumberOfItems();

        return array_slice($active_products, 0, $number_of_items);
    }

    /**
     * Get list of also viewed products for a product from manual recommendation.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return array
     */
    private function getManualRecommendation(string $shop_id, string $product_id): array
    {
        $list_product_ids = $this->product_query->getManualProducts($product_id);
        return $this->product_query->getActiveProducts($list_product_ids);
    }

    /**
     * Get list of manual product gid for a product.
     *
     * @param string $product_id
     * @return array
     */
    public function getManualProducts(string $product_id): array
    {
        $product_ids = $this->product_query->getManualProducts($product_id);
        return $this->product_query->getListGidByIds($product_ids);
    }

    /**
     * Get list of also viewed products for a product from relationship product.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return array
     */
    private function getDefaultRecommendation(string $shop_id, string $product_id): array
    {
        $product = $this->product_query->getById($product_id);
        $default_ids = $product->getDefaultRecommendationIds();

        $active_products = $this->product_query->getActiveProducts($default_ids);
        $number_of_items = $this->shop_query->getById($shop_id)->settings()->get()->getNumberOfItems();

        return collect($active_products)->random(min(count($active_products), $number_of_items))->toArray();
    }

    /**
     * Set list of recommended products for a product.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param array $list_recommended_gid
     * @param string|null $recommendation_type
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendedProducts(
        string $shop_id,
        string $product_id,
        array $list_recommended_gid,
        ?string $recommendation_type,
    ): ProductCollection {
        $recommendation_type = RecommendationType::tryFrom($recommendation_type);
        $recommended_ids = $this->product_query->validateListProductGid($shop_id, $list_recommended_gid);
        $product = $this->product_query->getById($product_id);
        $recommended_ids = array_unique(array_filter($recommended_ids, fn($id) => (string)$id !== $product_id));

        $this->product_command->setManualProduct(
            $product,
            $shop_id,
            $recommended_ids,
            $recommendation_type ?? $product->getRecommendationType(),
        );

        return $product;
    }

    /**
     * Set the recommendation type for a product.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param string $recommendation_type
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendationType(
        string $shop_id,
        string $product_id,
        string $recommendation_type,
    ): ProductCollection {
        $this->product_query->validateProductId($shop_id, $product_id);

        $recommendation_type = RecommendationType::tryFrom($recommendation_type);
        $product = $this->product_query->getById($product_id);

        $this->product_command->setRecommendationType($product, $recommendation_type);

        return $product;
    }

    /**
     * Get full information of a product (including recommendations).
     *
     * @param string $shop_id
     * @param string $product_id
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getFullInfo(string $shop_id, string $product_id): array
    {
        $this->product_query->validateProductId($shop_id, $product_id);

        $product = $this->product_query->getById($product_id);
        $recommended_products = $this->product_query->getManualProducts($product_id);
        // relative products, ....

        $product->setAttribute(
            'recommended_products',
            $this->product_service->fetchByIds($this->product_query->getListGidByIds($recommended_products)),
        );

        return $product->toArray();
    }

    /**
     * Get settings for auto recommendation.
     *
     * @param string $shop_id
     * @return array
     */
    public function getShopSettings(string $shop_id): array
    {
        $shop = $this->shop_query->getById($shop_id);
        return $this->shop_query->getShopSettings($shop);
    }

    /**
     * Set auto recommendation for a shop.
     *
     * @param string $shop_id
     * @param array $settings
     *
     * @return array
     */
    public function setShopSettings(
        string $shop_id,
        array $settings,
    ): array {
        $shop = $this->shop_query->getById($shop_id);
        return $this->shop_setting_command->setShopSettings($shop, $settings);
    }

    /**
     * Activate recommendation for all product of a shop.
     *
     * @param string $shop_id
     * @param string $status
     * @return bool
     */
    public function activateRecommendation(
        string $shop_id,
        string $status,
    ): bool {
        $status = RecommendationState::from($status);
        if ($status === $this->getShopRecommendationState($shop_id)) {
            return true;
        }

        match ($status) {
            RecommendationState::ACTIVE => $this->activate($shop_id),
            RecommendationState::INACTIVE => $this->deactivate($shop_id),
        };

        return true;
    }

    /**
     * Activate recommendation for all product of a shop.
     *
     * @param string $shop_id
     * @return void
     */
    private function activate(string $shop_id): void
    {
        $this->settingShop($shop_id, RecommendationState::ACTIVE);
        $this->product_command->activateRecommendation($shop_id);
    }

    /**
     * Deactivate recommendation for all product of a shop.
     *
     * @param string $shop_id
     * @return void
     */
    private function deactivate(string $shop_id): void
    {
        $this->settingShop($shop_id, RecommendationState::INACTIVE);
        $this->product_command->deactivateRecommendation($shop_id);
    }

    /**
     * Set recommendation state for a shop.
     *
     * @param string $shop_id
     * @param RecommendationState $status
     * @return void
     */
    private function settingShop(string $shop_id, RecommendationState $status): void
    {
        $this->shop_setting_command->setRecommendationState($shop_id, $status);
    }

    /**
     * Get shop recommendation state.
     *
     * @param string $shop_id
     * @return RecommendationState
     */
    private function getShopRecommendationState(string $shop_id): RecommendationState
    {
        return $this->shop_query->getById($shop_id)
            ->settings()->get()->getAutoRecommendation();
    }

    /**
     * Update product default recommendation for a shop.
     *
     * @param array $recommendation_data
     * @param array $map_gid_id
     * @return bool
     */
    public function updateManyDefaultRecommendation(array $recommendation_data, array $map_gid_id): bool
    {
        return $this->product_command->updateManyDefaultRecommendation(
            $this->matchData($recommendation_data, $map_gid_id, 'default_recommendation_ids'),
        );
    }

    /**
     * Update product recommendation for a shop.
     *
     * @param array $recommendation_data
     * @param array $map_gid_id
     * @return bool
     */
    public function updateManyRecommendation(array $recommendation_data, array $map_gid_id): bool
    {
        return $this->product_command->updateManyRecommendation(
            $this->matchData($recommendation_data, $map_gid_id, 'recommendation_ids'),
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
     * @param array $recommendation_data
     * @param array $product_data
     * @param string $attribute
     * @return array
     */
    private function matchData(array $recommendation_data, array $product_data, string $attribute): array
    {
        $result = [];
        foreach ($product_data as $gid => $id) {
            $formatted_gid = $this->formatShopifyId($gid);
            if (!isset($recommendation_data[$formatted_gid])) {
                continue;
            }

            $recommendations_gis = $recommendation_data[$formatted_gid];
            $recommendations = array_map(fn($item) => $product_data[Utils::getIdFromGid($item)], $recommendations_gis);

            $result[$id] = $recommendations;
        }

        return $result;
    }
}
