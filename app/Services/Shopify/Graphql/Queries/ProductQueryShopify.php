<?php

namespace App\Services\Shopify\Graphql\Queries;

use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\Exceptions\ShopifyGraphqlException;
use App\Lib\Utils;
use App\Objects\Enums\ShopifyType;
use App\Services\Shopify\Graphql\BaseGraphqlService;
use JsonException;
use Shopify\Exception\HttpRequestException;
use Shopify\Exception\MissingArgumentException;

class ProductQueryShopify extends BaseGraphqlService implements IProductQueryShopify
{
    /**
     * Define the items per page.
     */
    private const ITEMS_PER_PAGE = 250;

    /**
     * Define the product fields.
     */
    private const PRODUCT_FIELDS = <<<'GRAPHQL'
                      id
                      handle
                      status
                      title
                      description
                      vendor
                      productType
                      featuredMedia {
                          preview {
                              image {
                                  url
                              }
                          }
                      }
                      GRAPHQL;

    /**
     * Fetch all products from the shop.
     *
     * @return array
     * @throws ShopifyGraphqlException
     * @throws JsonException
     * @throws HttpRequestException
     * @throws MissingArgumentException
     */
    public function fetchAll(): array
    {
        $params = [
            'first' => self::ITEMS_PER_PAGE,
        ];
        $query = $this->buildProductQuery();

        $products = $this->all($query, $params);

        return array_map([$this, 'formatProduct'], $products);
    }

    /**
     * Fetch a product by its ID.
     *
     * @param string $id
     * @return array
     * @throws ShopifyGraphqlException
     * @throws JsonException
     * @throws HttpRequestException
     * @throws MissingArgumentException
     */
    public function fetchById(string $id): array
    {
        $query = $this->buildProductByIdQuery($id);

        $product = $this->graphql($query);

        return $this->formatProduct($product['data']['node']);
    }

    /**
     * Format Shopify product ID
     *
     * @param string $id
     * @return string
     */
    private function formatShopifyId(string $id): string
    {
        return Utils::addPrefixGraphId($id, ShopifyType::PRODUCT);
    }

    /**
     * Format the product data.
     *
     * @param array $product
     * @return array
     */
    private function formatProduct(array $product): array
    {
        $product['variants'] = $product['variants']['nodes'] ?? [];
        return $product;
    }

    /**
     * Fetch a list of products by their IDs.
     *
     * @param array $ids
     * @return array
     * @throws ShopifyGraphqlException
     * @throws JsonException
     * @throws HttpRequestException
     * @throws MissingArgumentException
     */
    public function fetchByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $query = $this->buildProductsByIdsQuery($ids);

        $products = $this->graphql($query);

        return array_map([$this, 'formatProduct'],
            array_filter($products['data']['nodes'], fn($product) => $product !== null));
    }

    /**
     * Build the query to fetch products by IDs.
     *
     * @param array $ids
     * @return string
     */
    private function buildProductsByIdsQuery(array $ids): string
    {
        $formattedIds = array_map([$this, 'formatShopifyId'], $ids);

        return sprintf(<<<'GRAPHQL'
                           query Products {
                               nodes(ids: ["%s"]) {
                                   ... on Product {
                                       %s
                                   }
                               }
                           }
                           GRAPHQL, implode('", "', $formattedIds), self::PRODUCT_FIELDS);
    }

    /**
     * Build the query to fetch products.
     *
     * @return string
     */
    private function buildProductQuery(): string
    {
        return sprintf(<<<'GRAPHQL'
                           query Products($first: Int!, $after: String) {
                               products(first: $first, after: $after) {
                                   nodes {
                                       %s
                                   }
                                   pageInfo {
                                       hasNextPage
                                       endCursor
                                   }
                               }
                           }
                           GRAPHQL, ProductQueryShopify::PRODUCT_FIELDS);
    }

    /**
     * Build the query to fetch a product by ID.
     *
     * @param string $id
     * @return string
     */
    private function buildProductByIdQuery(string $id): string
    {
        return sprintf(<<<'GRAPHQL'
                           query Product {
                               node(id: "%s") {
                                   id
                                   ... on Product {
                                       %s
                                   }
                               }
                           }
                           GRAPHQL, $this->formatShopifyId($id), self::PRODUCT_FIELDS);
    }
}
