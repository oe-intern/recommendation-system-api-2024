<?php

namespace App\Storage\Commands;

use App\Collections\ShopCollection;
use App\Contracts\Commands\IShopCommand;

class ShopCommand implements IShopCommand
{
    /**
     * Create a shop.
     *
     * @param string $shop_domain
     * @return ShopCollection
     */
    public function create(string $shop_domain): ShopCollection
    {
        return ShopCollection::query()
            ->create(['domain' => $shop_domain]);
    }
}
