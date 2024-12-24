<?php

namespace App\Contracts\Queries;

use App\Collections\ShopCollection;
use App\Exceptions\ShopNotFoundException;

interface IShopQuery
{
    /**
     * Get a shop by domain.
     *
     * @param string $shopDomain
     * @return ShopCollection|null
     */
    public function getByDomain(string $shopDomain): ?ShopCollection;

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
     * @param string $shopId
     * @return ShopCollection|null
     */
    public function getById(string $shopId): ?ShopCollection;

    /**
     * Get a shop ID by domain.
     *
     * @param string $shopDomain
     * @return string
     *
     * @throws ShopNotFoundException
     */
    public function getShopIdByDomain(string $shopDomain): string;
}
