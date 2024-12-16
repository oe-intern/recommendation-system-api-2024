<?php

namespace App\Contracts\Recommendation;

use App\Collections\ShopCollection;

interface IProduct
{
    /**
     * Handle update product if exist and create if not
     *
     * @param ShopCollection $shop
     * @param array $product_data
     * @return void
     */
    public function createOrUpdateMany(ShopCollection $shop, array $product_data): void;
}
