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
     * @param string $shop_domain
     * @return ShopCollection|null
     */
    public function getByDomain(string $shop_domain): ?ShopCollection
    {
        return ShopCollection::query()
            ->where('domain', $shop_domain)
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
     * @param string $shop_id
     * @return ShopCollection|null
     */
    public function getById(string $shop_id): ?ShopCollection
    {
        return ShopCollection::query()
            ->where('_id', $shop_id)
            ->first();
    }

    /**
     * Get a shop by domain.
     *
     * @param string $shop_domain
     * @return string
     *
     * @throws ShopNotFoundException
     */
    public function getShopIdByDomain(string $shop_domain): string
    {
        $shop = $this->getByDomain($shop_domain);
        if (!$shop) {
            throw new ShopNotFoundException($shop_domain);
        }

        return $shop->getId();
    }
}
