<?php

namespace App\Services\Shopify\Graphql\Queries;

use App\Contracts\Shopify\Graphql\Queries\Product as IProduct;
use App\Exceptions\ShopifyGraphqlException;
use App\Services\Shopify\Graphql\BaseGraphqlService;
use JsonException;
use Shopify\Exception\HttpRequestException;
use Shopify\Exception\MissingArgumentException;

class ProductService extends BaseGraphqlService implements IProduct
{
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
        $limit = 250;
        $params = [
            'first' => $limit,
        ];

        $query = <<<'GRAPHQL'
                    query Products($first: Int!) {
                        products(first: $first) {
                            nodes {
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
                                    }
                                }
                                title
                                tags
                                status
                                productType
                                totalInventory
                                description
                            }
                            pageInfo {
                                hasNextPage
                                endCursor
                            }
                        }
                    }
                GRAPHQL;

        $products = $this->all($query, $params);

        return array_map(function ($product) {
            $product['variants'] = $product['variants']['nodes'] ?? [];
            return $product;
        }, $products);
    }
}
