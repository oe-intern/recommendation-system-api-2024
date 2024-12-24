<?php

namespace App\Storage\Queries;

use App\Collections\ShopCollection;
use App\Contracts\Queries\IShopQuery;
use App\Exceptions\ShopNotFoundException;

class ShopQuery implements IShopQuery
{
    /**
     * Get a shop by domain.
     *
     * @param string $shopDomain
     * @return ShopCollection|null
     */
    public function getByDomain(string $shopDomain): ?ShopCollection
    {
        return ShopCollection::query()
            ->where('domain', $shopDomain)
            ->first();
    }

    /**
     * Get the auto recommendation settings for a shop.
     *
     * @param ShopCollection $shop
     * @return array
     */
    public function getShopSettings(ShopCollection $shop): array
    {
        $settings = $shop->settings()->get();
        return $settings ? $settings->toArray() : [];
    }

    /**
     * Get a shop by ID.
     *
     * @param string $shopId
     * @return ShopCollection|null
     */
    public function getById(string $shopId): ?ShopCollection
    {
        return ShopCollection::query()
            ->where('_id', $shopId)
            ->first();
    }

    /**
     * Get a shop by domain.
     *
     * @param string $shopDomain
     * @return string
     *
     * @throws ShopNotFoundException
     */
    public function getShopIdByDomain(string $shopDomain): string
    {
        $shop = $this->getByDomain($shopDomain);
        if (!$shop) {
            throw new ShopNotFoundException($shopDomain);
        }

        return $shop->getId();
    }
}
