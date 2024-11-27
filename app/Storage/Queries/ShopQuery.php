<?php

namespace App\Storage\Queries;

use App\Collections\ShopCollection;
use App\Contracts\Queries\IShopQuery;

class ShopQuery implements IShopQuery
{
    /**
     * Get a shop by domain.
     *
     * @param string $shop_domain
     * @return ShopCollection|null
     */
    public function getByDomain(string $shop_domain): ?ShopCollection
    {
        return ShopCollection::query()
            ->where('domain', $shop_domain)
            ->first();
    }
}
