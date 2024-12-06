<?php

namespace App\Services\Shopify\Graphql\Queries;

use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\Exceptions\ShopifyGraphqlException;
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
            title
            handle
            category {
                id
            }
            vendor
            variants(first: 250) {
                nodes {
                    id
                    title
                    taxCode
                    price
                    image {
                        id
                        altText
                        url
                    }
                }
            }
            title
            tags
            status
            productType
            totalInventory
            description
            featuredMedia {
                id
                alt
                mediaContentType
                preview {
                    image {
                        url
                    }
                }
            }
            priceRangeV2 {
                maxVariantPrice {
                    amount
                    currencyCode
                }
                minVariantPrice {
                    amount
                    currencyCode
                }
            }
            handle
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

        $query = <<<'GRAPHQL'
            query Products($first: Int!) {
                products(first: $first) {
                    nodes {
                        %s
                    }
                    pageInfo {
                        hasNextPage
                        endCursor
                    }
                }
            }
        GRAPHQL;

        // Inject the product fields into the query.
        $query = sprintf($query, self::PRODUCT_FIELDS);

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
        $query = <<<'GRAPHQL'
                query Product {
                    node(id: "%s") {
                        id
                        ... on Product {
                            %s
                        }
                    }
                }
            GRAPHQL;

        // Inject the product fields into the query.
        $query = sprintf($query, $this->formatShopifyId($id), self::PRODUCT_FIELDS);

        $product = $this->graphql($query);

        return $this->formatProduct($product['data']['node']);
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

        $query = <<<'GRAPHQL'
                query Products {
                    nodes(ids: ["%s"]) {
                        ... on Product {
                            %s
                        }
                    }
                }
            GRAPHQL;

        $formattedIds = array_map([$this, 'formatShopifyId'], $ids);

        // Inject the product fields into the query.
        $query = sprintf($query, implode('", "', $formattedIds), self::PRODUCT_FIELDS);

        $products = $this->graphql($query);

        return array_map([$this, 'formatProduct'], array_filter($products['data']['nodes'], fn($product) => $product !== null));
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
}
