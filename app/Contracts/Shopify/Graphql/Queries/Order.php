<?php

namespace App\Contracts\Shopify\Graphql\Queries;

interface Order
{
    /**
     * Fetch all orders of a shop.
     *
     * @return array
     */
    public function fetchAll(): array;
}
