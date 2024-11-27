<?php

namespace App\Contracts\Queries;

use App\Collections\ShopCollection;

interface IShopQuery
{
    /**
     * Get a shop by domain.
     *
     * @param string $shop_domain
     * @return ShopCollection|null
     */
    public function getByDomain(string $shop_domain): ?ShopCollection;
}
