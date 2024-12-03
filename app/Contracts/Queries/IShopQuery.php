<?php

namespace App\Contracts\Queries;

use App\Collections\ShopCollection;
use App\Exceptions\ShopNotFoundException;

interface IShopQuery
{
    /**
     * Get a shop by domain.
     *
     * @param string $shop_domain
     * @return ShopCollection|null
     */
    public function getByDomain(string $shop_domain): ?ShopCollection;

    /**
     * Get the auto recommendation settings for a shop.
     *
     * @param ShopCollection $shop
     * @return array
     */
    public function getShopSettings(ShopCollection $shop): array;

    /**
     * Get a shop by ID.
     *
     * @param string $shop_id
     * @return ShopCollection|null
     */
    public function getById(string $shop_id): ?ShopCollection;

    /**
     * Get a shop ID by domain.
     *
     * @param string $shop_domain
     * @return string
     *
     * @throws ShopNotFoundException
     */
    public function getShopIdByDomain(string $shop_domain): string;
}
