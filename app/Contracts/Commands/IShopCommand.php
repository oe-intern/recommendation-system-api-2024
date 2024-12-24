<?php

namespace App\Contracts\Commands;

use App\Collections\ShopCollection;

interface IShopCommand
{
    /**
     * Create a shop.
     *
     * @param string $shopDomain
     * @return ShopCollection
     */
    public function create(string $shopDomain): ShopCollection;
}
