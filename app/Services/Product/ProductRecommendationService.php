<?php

namespace App\Services\Product;

use App\Collections\ProductCollection;
use App\Contracts\Commands\IProductCommand;
use App\Contracts\Commands\IRelationshipScoreCommand;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IRelationshipScoreQuery;
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
     * @var IRelationshipScoreQuery
     */
    protected IRelationshipScoreQuery $relationship_score_query;

    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * @var IRelationshipScoreCommand
     */
    protected IRelationshipScoreCommand $relationship_score_command;

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
     * @param IRelationshipScoreQuery $relationship_score_query
     * @param IShopQuery $shop_query
     * @param IRelationshipScoreCommand $relationship_score_command
     * @param IProductCommand $product_command
     * @param IProductQueryShopify $product_service
     * @param IShopCommand $shop_command
     */
    public function __construct(
        IProductQuery $product_query,
        IRelationshipScoreQuery $relationship_score_query,
        IShopQuery $shop_query,
        IRelationshipScoreCommand $relationship_score_command,
        IProductCommand $product_command,
        IProductQueryShopify $product_service,
        IShopCommand $shop_command,
    ) {
        $this->product_query = $product_query;
        $this->relationship_score_query = $relationship_score_query;
        $this->shop_query = $shop_query;
        $this->relationship_score_command = $relationship_score_command;
        $this->product_command = $product_command;
        $this->product_service = $product_service;
        $this->shop_command = $shop_command;
    }

    /**
     * Get list of recommended products for a product.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getRecommendedProducts(string $shop_domain, string $product_id): array
    {
        $product = $this->product_query->getByShopDomainAndId($shop_domain, $product_id);

        if (!$product) {
            throw new ProductNotFoundException($shop_domain, $product_id);
        }

        $recommendation_type = $product->getAttribute('recommendationType');

        return match ($recommendation_type) {
            RecommendationType::AUTO => $this->getAutoRecommendation($shop_domain, $product_id),
            RecommendationType::MANUAL => $this->getManualRecommendation($shop_domain, $product_id),
            default => $this->getDefinedRecommendation($shop_domain, $product_id),
        };
    }

    /**
     * Get list of also viewed products for a product from system recommendation.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return array
     */
    private function getAutoRecommendation(string $shop_domain, string $product_id): array
    {
        $referenced_products = ["8909707706614", "8909708558582", "8909708689654", "8909707837686"];

        return $this->product_service->fetchByIds($referenced_products);
    }

    /**
     * Get list of also viewed products for a product from manual recommendation.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return array
     */
    private function getManualRecommendation(string $shop_domain, string $product_id): array
    {
        $optional_products = $this->product_query->getOptionalProducts($product_id);

        return $this->product_service->fetchByIds($optional_products);
    }

    /**
     * Get list of also viewed products for a product from relationship product.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return array
     */
    private function getDefinedRecommendation(string $shop_domain, string $product_id): array
    {
        $recommended_products = ["8909707706614", "8909708558582", "8909708689654", "8909707837686"];

        return $this->product_service->fetchByIds($recommended_products);
    }

    /**
     * Set list of recommended products for a product.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @param array $recommended_products
     * @param string|null $recommendation_type
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendedProducts(
        string $shop_domain,
        string $product_id,
        array $recommended_products,
        ?string $recommendation_type
    ): ProductCollection {
        $this->product_query->validateProductIds($shop_domain, array_merge([$product_id], $recommended_products));

        $recommendation_type = RecommendationType::tryFrom($recommendation_type);
        $product = $this->product_query->getById($product_id);

        $this->product_command->setRecommendationProduct(
            $product, $shop_domain,
            $recommendation_type ?? $product->getType(),
            $recommended_products
        );

        return $product;
    }

    /**
     * Set the recommendation type for a product.
     *
     * @param string $shop_domain
     * @param string $product_id
     * @param string $recommendation_type
     * @return ProductCollection
     *
     * @throws ProductNotFoundException
     */
    public function setRecommendationType(
        string $shop_domain,
        string $product_id,
        string $recommendation_type
    ): ProductCollection {
        $this->product_query->validateProductId($shop_domain, $product_id);

        $recommendation_type = RecommendationType::tryFrom($recommendation_type);
        $product = $this->product_query->getById($product_id);

        $this->product_command->setRecommendationType($product, $recommendation_type);

        return $product;
    }

    /**
     * Get full information of a product (including recommendations).
     *
     * @param string $shop_domain
     * @param string $product_id
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getFullInfo(string $shop_domain, string $product_id): array
    {
        $this->product_query->validateProductId($shop_domain, $product_id);

        $product = $this->product_query->getById($product_id);
        $recommended_products = $this->product_query->getOptionalProducts($product_id);
        // relative products, ....

        $product->setAttribute('recommended_products', $this->product_service->fetchByIds($recommended_products));

        return $product->toArray();
    }

    /**
     * Get settings for auto recommendation.
     *
     * @param string $shop_domain
     * @return array
     */
    public function getAutoRecommendationSettings(string $shop_domain): array
    {
        $shop = $this->shop_query->getByDomain($shop_domain);
        return $this->shop_query->getAutoRecommendationSettings($shop);
    }

    /**
     * Set auto recommendation for a shop.
     *
     * @param string $shop_domain
     * @param array $settings
     *
     * @return array
     */
    public function setAutoRecommendationSettings(
        string $shop_domain,
        array $settings
    ): array {
        $shop = $this->shop_query->getByDomain($shop_domain);
        return $this->shop_command->setAutoRecommendationSettings(
            $shop,
            $this->transformRequestSettings($settings)
        );
    }

    /**
     * Transform request settings to array data for ShopSettingScheme.
     *
     * @param array $settings
     * @return array
     */
    private function transformRequestSettings(array $settings): array
    {
        return [
            'layout' => $settings['layout'],
            'backgroundColor' => $settings['background_color'],
            'textColor' => $settings['text_color'],
            'numberOfItems' => (int) $settings['number_of_items']
        ];
    }
}
