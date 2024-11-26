<?php

namespace App\Services\Product;

use App\Collections\Product as ProductCollection;
use App\Contracts\Commands\Product as ProductCommand;
use App\Contracts\Commands\RelationshipScore as RelationshipScoreCommand;
use App\Contracts\Queries\Product as ProductQuery;
use App\Contracts\Queries\RelationshipScore as RelationshipScoreQuery;
use App\Contracts\Queries\Shop as ShopQuery;
use App\Contracts\Recommendation\ProductRecommendation as IProductRecommendation;
use App\Contracts\Shopify\Graphql\Queries\Product as ProductService;
use App\Exceptions\ProductNotFoundException;
use App\Objects\Enums\RecommendationType;
use Illuminate\Support\Facades\Log;

class ProductRecommendationService implements IProductRecommendation
{
    /**
     * @var ProductQuery
     */
    protected ProductQuery $product_query;

    /**
     * @var RelationshipScoreQuery
     */
    protected RelationshipScoreQuery $relationship_score_query;

    /**
     * @var ShopQuery
     */
    protected ShopQuery $shop_query;

    /**
     * @var RelationshipScoreCommand
     */
    protected RelationshipScoreCommand $relationship_score_command;

    /**
     * @var ProductCommand
     */
    protected ProductCommand $product_command;

    /**
     * @var ProductService
     */
    protected ProductService $product_service;

    /**
     * ProductRecommendationService constructor.
     *
     * @param ProductQuery $product_query
     * @param RelationshipScoreQuery $relationship_score_query
     * @param ShopQuery $shop_query
     * @param RelationshipScoreCommand $relationship_score_command
     * @param ProductCommand $product_command
     * @param ProductService $product_service
     */
    public function __construct(
        ProductQuery $product_query,
        RelationshipScoreQuery $relationship_score_query,
        ShopQuery $shop_query,
        RelationshipScoreCommand $relationship_score_command,
        ProductCommand $product_command,
        ProductService $product_service
    ) {
        $this->product_query = $product_query;
        $this->relationship_score_query = $relationship_score_query;
        $this->shop_query = $shop_query;
        $this->relationship_score_command = $relationship_score_command;
        $this->product_command = $product_command;
        $this->product_service = $product_service;
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
        $product_id = '8909707542774';
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
     * @param RecommendationType|null $recommendation_type
     * @return ProductCollection
     */
    public function setRecommendedProducts(
        string $shop_domain,
        string $product_id,
        array $recommended_products,
        ?RecommendationType $recommendation_type
    ): ProductCollection {
        Log::info("==================".$shop_domain."==================".$product_id."==================");
        $product = $this->product_query->getById($product_id);
        Log::info("==================".json_encode($product)."==================");
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
     * @param string $product_id
     * @param RecommendationType $recommendation_type
     * @return ProductCollection
     */
    public function setRecommendationType(
        string $product_id,
        RecommendationType $recommendation_type
    ): ProductCollection {
        $product = $this->product_query->getById($product_id);

        $this->product_command->setRecommendationType($product, $recommendation_type);

        return $product;
    }

    /**
     * Get full information of a product (including recommendations).
     *
     * @param string $product_id
     * @return array
     */
    public function getFullInfo(string $product_id): array
    {
        $product = $this->product_query->getById($product_id);
        $recommended_products = $this->product_query->getOptionalProducts($product_id);
        // relative products, ....

        $product->setAttribute('recommended_products', $this->product_service->fetchByIds($recommended_products));

        return $product->toArray();
    }
}
