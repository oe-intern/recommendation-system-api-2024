<?php

namespace App\Contracts\Queries;

use App\Collections\ProductCollection;

interface IInteractionQuery
{
    /**
     * Filter list of interactions for a shop or product.
     *
     *
     * @param string $shop_domain
     * @param ProductCollection|null $product
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public  function filter(string $shop_domain, ?ProductCollection $product, string $start_date, string $end_date): array;
}
