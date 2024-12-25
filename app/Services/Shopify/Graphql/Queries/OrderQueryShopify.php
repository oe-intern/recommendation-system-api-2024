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

        $query = $this->buildOrdersQuery();

        $orders = $this->all($query, $params);

        return $this->processOrders($orders);
    }

    /**
     * Build the GraphQL query for fetching orders.
     *
     * @return string
     */
    private function buildOrdersQuery(): string
    {
        return <<<'GRAPHQL'
            query Orders($first: Int!, $after: String) {
                orders(first: $first, after: $after) {
                    nodes {
                        id
                        lineItems(first: 250) {
                            nodes {
                                id
                                product {
                                    id
                                    productType
                                }
                            }
                        }
                    }
                    pageInfo {
                        hasNextPage
                        endCursor
                    }
                }
            }
        GRAPHQL;
    }

    /**
     * Process the fetched orders to map line items.
     *
     * @param array $orders
     * @return array
     */
    protected function processOrders(array $orders): array
    {
        return collect($orders)->map(function ($order) {
            $order['lineItems'] = collect($order['lineItems']['nodes']);
            return $order;
        })->toArray();
    }
}
