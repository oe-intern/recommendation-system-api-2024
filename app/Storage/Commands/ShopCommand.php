<?php

namespace App\Storage\Commands;

use App\Collections\ShopCollection;
use App\Contracts\Commands\IShopCommand;
use App\Collections\Schema\ShopSettingScheme;
use App\Contracts\Queries\IShopQuery;

class ShopCommand implements IShopCommand
{
    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * ShopCommand constructor.
     *
     * @param IShopQuery $shop_query
     */
    public function __construct(IShopQuery $shop_query)
    {
        $this->shop_query = $shop_query;
    }

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

    /**
     * Set the auto recommendation settings for a shop.
     *
     * @param ShopCollection $shop
     * @param array $settings
     * @return array
     */
    public function setAutoRecommendationSettings(ShopCollection $shop, array $settings): array
    {
        $new_settings = new ShopSettingScheme($settings);
        $shop->settings()->save($new_settings);

        return $shop->settings()->get()->toArray();
    }
}
