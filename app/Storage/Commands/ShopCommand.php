<?php

namespace App\Storage\Commands;

use App\Collections\Schema\ShopRecommendationSchema;
use App\Collections\Schema\ShopSettingSchema;
use App\Collections\ShopCollection;
use App\Contracts\Commands\IShopCommand;
use App\Lib\Utils;

class ShopCommand implements IShopCommand
{
    /**
     * Create a shop.
     *
     * @param string $shopDomain
     * @return ShopCollection
     */
    public function create(string $shopDomain): ShopCollection
    {
        $setting = $this->createShopSettingSchema();
        $shopRecommendation = $this->createShopRecommendationSchema();

        $shop = $this->createShop($shopDomain);

        $this->embedShop($shop, $setting, $shopRecommendation);
        return $shop;
    }

    /**
     * Create shop settings schema.
     *
     * @return ShopSettingSchema
     */
    private function createShopSettingSchema(): ShopSettingSchema
    {
        return new ShopSettingSchema();
    }

    /**
     * Create shop recommendation schema.
     *
     * @return ShopRecommendationSchema
     */
    private function createShopRecommendationSchema(): ShopRecommendationSchema
    {
        return new ShopRecommendationSchema([
            'expires_at' => Utils::refreshDay(),
            'refresh_count' => config('services.recommendation.refresh_limit') + 1,
        ]);
    }

    /**
     * Create a shop record.
     *
     * @param string $shopDomain
     * @return ShopCollection
     */
    private function createShop(string $shopDomain): ShopCollection
    {
        return ShopCollection::query()->create(['domain' => $shopDomain]);
    }

    /**
     * Embed shop settings and recommendation.
     *
     * @param ShopCollection $shop
     * @param ShopSettingSchema $setting
     * @param ShopRecommendationSchema $shopRecommendation
     * @return void
     */
    private function embedShop(
        ShopCollection $shop,
        ShopSettingSchema $setting,
        ShopRecommendationSchema $shopRecommendation,
    ): void {
        $this->embedSettings($shop, $setting);
        $this->embedRecommendation($shop, $shopRecommendation);
    }

    /**
     * Attach settings to a shop.
     *
     * @param ShopCollection $shop
     * @param ShopSettingSchema $setting
     * @return void
     */
    private function embedSettings(ShopCollection $shop, ShopSettingSchema $setting): void
    {
        $shop->settings()->create($setting->toArray());
    }

    /**
     * Attach recommendation to a shop.
     *
     * @param ShopCollection $shop
     * @param ShopRecommendationSchema $shopRecommendation
     * @return void
     */
    private function embedRecommendation(
        ShopCollection $shop,
        ShopRecommendationSchema $shopRecommendation,
    ): void {
        $shop->shopRecommendation()->create($shopRecommendation->toArray());
    }
}
