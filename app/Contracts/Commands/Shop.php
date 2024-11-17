<?php

namespace App\Contracts\Commands;

use App\Collections\Shop as ShopCollection;

interface Shop
{
    /**
     * Create a shop.
     *
     * @param string $shop_domain
     * @return ShopCollection
     */
    public function create(string $shop_domain): ShopCollection;
}
