<?php

namespace App\Storage\Queries;

use App\Collections\Shop as ShopCollection;
use App\Contracts\Queries\Shop as ShopQuery;

class Shop implements ShopQuery
{
    /**
     * Get a shop by domain.
     *
     * @param string $shop_domain
     * @return ShopCollection|null
     */
    public function getByDomain(string $shop_domain): ?ShopCollection
    {
        return ShopCollection::where('domain', $shop_domain)->first();
    }
}
