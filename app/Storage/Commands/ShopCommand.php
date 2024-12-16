<?php

namespace App\Storage\Commands;

use App\Collections\Schema\ShopSettingSchema;
use App\Collections\ShopCollection;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\Queries\IShopQuery;
use App\Objects\Enums\RecommendationState;

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
     * Set the recommendation state for a shop.
     *
     * @param string $shop_id
     * @param RecommendationState $state
     * @return void
     */
    public function setRecommendationState(string $shop_id, RecommendationState $state): void
    {
        ShopCollection::query()
            ->where('id', $shop_id)
            ->update([
                'recommendation_state' => $state->value,
            ]);
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
}
