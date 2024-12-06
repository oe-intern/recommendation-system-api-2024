<?php

namespace App\Services\Product;

use App\Collections\ProductCollection;
use App\Contracts\Commands\IProductCommand;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\Exceptions\ProductNotFoundException;
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
     * @var IShopCommand
     */
    protected IShopCommand $shop_command;

    /**
     * ProductRecommendationService constructor.
     *
     * @param IProductQuery $product_query
     * @param IShopQuery $shop_query
     * @param IProductCommand $product_command
     * @param IProductQueryShopify $product_service
     * @param IShopCommand $shop_command
     */
    public function __construct(
        IProductQuery $product_query,
        IShopQuery $shop_query,
        IProductCommand $product_command,
        IProductQueryShopify $product_service,
        IShopCommand $shop_command,
    ) {
        $this->product_query = $product_query;
        $this->shop_query = $shop_query;
        $this->product_command = $product_command;
        $this->product_service = $product_service;
        $this->shop_command = $shop_command;
    }

    /**
     * Get list of recommended products for a product.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getRecommendedProducts(string $shop_id, string $product_id): array
    {
        $product = $this->product_query->getByShopIdAndId($shop_id, $product_id);
        $recommendation_type = $product->getRecommendationType();

        return match ($recommendation_type) {
            RecommendationType::AUTO => $this->getAutoRecommendation($shop_id, $product_id),
            RecommendationType::MANUAL => $this->getManualRecommendation($shop_id, $product_id),
            default => $this->getDefaultRecommendation($shop_id, $product_id),
        };
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
        $fake_ids = [
            "67527565c54fb80ac00ae4f0",
            "67527568c54fb80ac00ae4f1",
            "67527573c54fb80ac00ae4f2",
            "6752757cc54fb80ac00ae4f3",
            "6752757dc54fb80ac00ae4f4",
            "67527584c54fb80ac00ae4f5"
        ];
        $number_of_items = $this->shop_query->getById($shop_id)->settings()->get()->getNumberOfItems();

        $random_products = collect($fake_ids)->random(min(count($fake_ids), $number_of_items));
        return $this->product_service->fetchByIds($this->product_query->getListGidByIds($random_products->toArray()));
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

        $list_product_gid = $this->product_query->getListGidByIds($list_product_ids);
        return $this->product_service->fetchByIds($list_product_gid);
    }

    /**
     * Get list of manual product gid for a product.
     *
     * @param string $shop_id
     * @param string $product_id
     * @return array
     */
    public function getManualProducts(string $shop_id, string $product_id): array
    {
        $product_ids = $this->getManualRecommendation($shop_id, $product_id);
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
        $fake_ids = [
            "67527565c54fb80ac00ae4f0",
            "67527568c54fb80ac00ae4f1",
            "67527573c54fb80ac00ae4f2",
            "6752757cc54fb80ac00ae4f3",
            "6752757dc54fb80ac00ae4f4",
            "67527584c54fb80ac00ae4f5"
        ];
        $number_of_items = $this->shop_query->getById($shop_id)->settings()->get()->getNumberOfItems();

        $random_products = collect($fake_ids)->random(min(count($fake_ids), $number_of_items));
        return $this->product_service->fetchByIds($this->product_query->getListGidByIds($random_products->toArray()));
    }

    /**
     * Set list of recommended products for a product.
     *
     * @param string $shop_id
     * @param string $product_id
     * @param array $list_recommended_gid
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendedProducts(
        string $shop_id,
        string $product_id,
        array $list_recommended_gid,
    ): ProductCollection {
        $recommended_ids = $this->product_query->validateListProductGid($shop_id, $list_recommended_gid);
        $product = $this->product_query->getById($product_id);
        $recommended_ids = array_unique(array_filter($recommended_ids, fn($id) => (string)$id !== $product_id));

        $this->product_command->setManualProduct(
            $product,
            $shop_id,
            $recommended_ids
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
        string $recommendation_type
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

        $product->setAttribute('recommended_products', $this->product_service->fetchByIds(
            $this->product_query->getListGidByIds($recommended_products)
        ));

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
        array $settings
    ): array {
        $shop = $this->shop_query->getById($shop_id);
        return $this->shop_command->setShopSettings(
            $shop,
            $settings,
        );
    }
}
