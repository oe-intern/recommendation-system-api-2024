<?php

namespace App\Contracts\Recommendation;

use App\Collections\ShopCollection;

interface IProduct
{
    /**
     * Handle update product if exist and create if not
     *
     * @param ShopCollection $shop
     * @param array $productData
     * @return void
     */
    public function createOrUpdateMany(ShopCollection $shop, array $productData): void;
}
