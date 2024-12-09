<?php

namespace App\Services\Shopify\Graphql\Queries;

use App\Contracts\Shopify\Graphql\Queries\IOrderQueryShopify;
use App\Exceptions\ShopifyGraphqlException;
use App\Services\Shopify\Graphql\BaseGraphqlService;
use JsonException;
use Shopify\Exception\HttpRequestException;
use Shopify\Exception\MissingArgumentException;

class OrderQueryShopify extends BaseGraphqlService implements IOrderQueryShopify
{
    /**
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
                    query Orders($first: Int!, $after: String) {
                        orders(first: $first, after: $after) {
                            nodes {
                                id
                                email
                                lineItems(first: 250) {
                                    nodes {
                                        id
                                        name
                                        quantity
                                    }
                                }
                                totalPriceSet {
                                    presentmentMoney {
                                        amount
                                        currencyCode
                                    }
                                }
                                createdAt
                            }
                            pageInfo {
                                hasNextPage
                                endCursor
                            }
                        }
                    }
                GRAPHQL;

        $orders = $this->all($query, $params);

        return collect($orders)->map(function ($order) {
            $order['lineItems'] = collect($order['lineItems']['nodes']);
            return $order;
        })->toArray();

    }
}
