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
}
