<?php

namespace App\Storage\Commands;

use App\Collections\Shop as ShopCollection;
use App\Contracts\Commands\Shop as ShopCommand;

class Shop implements ShopCommand
{
    /**
     * Create a shop.
     *
     * @param string $shop_domain
     * @return ShopCollection
     */
    public function create(string $shop_domain): ShopCollection
    {
        return ShopCollection::create([
            'domain' => $shop_domain,
        ]);
    }
}
