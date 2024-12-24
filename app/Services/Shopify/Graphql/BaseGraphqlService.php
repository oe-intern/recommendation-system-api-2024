<?php

namespace App\Services\Shopify\Graphql;

use App\Exceptions\ShopifyGraphqlException;
use App\Exceptions\ShopifyGraphqlUserError;
use App\Services\Shopify\UserContext;
use Illuminate\Support\Arr;
use Shopify\Clients\Graphql;
use Shopify\Context;
use Shopify\Exception\HttpRequestException;
use Shopify\Exception\MissingArgumentException;

abstract class BaseGraphqlService
{
    /**
     * @var UserContext
     */
    protected UserContext $userContext;

    /**
     * Create a new BaseGraphqlService instance.
     */
    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * Set the API version.
     *
     * @return void
     */
    public function setVersion($version)
    {
        Context::$API_VERSION = $version;
    }

    /**
     * Get the Graphql client.
     *
     * @param $data
     * @param array $query
     * @param array $extraHeaders
     * @param int|null $tries
     * @return mixed
     * @throws MissingArgumentException
     * @throws ShopifyGraphqlException
     * @throws \JsonException
     * @throws HttpRequestException
     */
    public function graphql($data, array $query = [], array $extraHeaders = [], ?int $tries = null)
    {
        $client = new Graphql(
            $this->userContext->getDomain()->toNative(),
            $this->userContext->getAccessToken()->toNative()
        );

        $response = $client->query($data, $query, $extraHeaders);
        $response = $response->getDecodedBody();

        $container = data_get($response, 'data');
        $firstKey = array_key_first((array) $container);
        $userErrors = data_get($container, $firstKey . '.userErrors');

        if ($userErrors) {
            throw new ShopifyGraphqlUserError($userErrors);
        }

        $maxTries = config('shopify-app.graphql_max_tries', 3);

        if ($errors = data_get($response, 'errors')) {
            if (count($errors) === 1) {
                $errorCode = data_get($errors, '0.extensions.code');

                if ($errorCode === 'THROTTLED' && $tries < $maxTries) {
                    sleep(1);

                    return $this->graphql($data, $query, $extraHeaders, $tries + 1);
                }
            }

            throw new ShopifyGraphqlException($errors);
        }

        return $response;
    }

    /**
     * Get all data
     *
     * @param string $query
     * @param $params
     * @return array
     * @throws HttpRequestException
     * @throws MissingArgumentException
     * @throws ShopifyGraphqlException
     * @throws \JsonException
     */
    public function all(string $query, $params)
    {
        $limit = data_get($params, 'first', 10);
        $result = [];

        do {
            $params['first'] = $limit;
            $response = $this->graphql(['query' => $query, 'variables' => $params]);
            $data = data_get($response, 'data', []);
            $element = Arr::first($data);
            $nodes = data_get($element, 'nodes', []);
            $result = array_merge($result, $nodes);
            $pageInfo = data_get($element, 'pageInfo', []);
            $hasNextPage = data_get($pageInfo, 'hasNextPage', false);

            if ($hasNextPage) {
                $nextCursor = Arr::get($pageInfo, 'endCursor');
                $params['after'] = $nextCursor;
            }
        } while ($hasNextPage);

        return $result;
    }
}
