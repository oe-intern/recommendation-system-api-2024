<?php

namespace App\Storage\Commands;

use App\Collections\Schema\ShopSettingSchema;
use App\Collections\ShopCollection;
use App\Contracts\Commands\IShopCommand;
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
     * Set the auto recommendation settings for a shop.
     *
     * @param ShopCollection $shop
     * @param array $settings
     * @return array
     */
    public function setShopSettings(ShopCollection $shop, array $settings): array
    {
        $shop_settings = $shop->settings()->get();

        if (!$shop_settings) {
            $shop_settings = $shop->settings()->create($settings);
        } else {
            $shop_settings->fill($settings);
            $shop_settings->save();
        }

        return $shop_settings->toArray();
    }

    /**
     * Create a shop.
     *
     * @param string $shop_domain
     * @return ShopCollection
     */
    public function create(string $shop_domain): ShopCollection
    {
        $setting = new ShopSettingSchema();
        $shop = ShopCollection::query()
            ->create([
                'domain' => $shop_domain,
            ]);
        $shop->settings()->create($setting->toArray());
        return $shop;
    }

    /**
     * Update the last job recommendation by shop id.
     *
     * @param string $shop_id
     * @param string $job_recommendation_id
     *
     * @return bool
     */
    public function updateLastJobRecommendation(string $shop_id, string $job_recommendation_id): bool
    {
        return ShopCollection::query()
            ->where('id', $shop_id)
            ->update([
                'last_job_recommendation_id' => $job_recommendation_id,
            ]);
    }
}
