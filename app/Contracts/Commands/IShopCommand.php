<?php

namespace App\Contracts\Commands;

use App\Collections\ShopCollection;

interface IShopCommand
{
    /**
     * Create a shop.
     *
     * @param string $shop_domain
     * @return ShopCollection
     */
    public function create(string $shop_domain): ShopCollection;

    /**
     * Set the auto recommendation settings for a shop.
     *
     * @param ShopCollection $shop
     * @param array $settings
     * @return array
     */
    public function setShopSettings(ShopCollection $shop, array $settings): array;
}
