<?php

namespace App\Contracts\Shopify\Graphql\Queries;

interface Product
{
    /**
     * Fetch all products of a shop.
     *
     * @return array
     */
    public function fetchAll(): array;

    /**
     * Fetch a product by its ID.
     *
     * @param string $id
     * @return array
     */
    public function fetchById(string $id): array;

    /**
     * Fetch a list of products by their IDs.
     *
     * @param array $ids
     * @return array
     */
    public function fetchByIds(array $ids): array;
}
