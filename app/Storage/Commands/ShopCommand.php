<?php

namespace App\Storage\Commands;

use App\Collections\Schema\ShopSettingSchema;
use App\Collections\ShopCollection;
use App\Contracts\Commands\IShopCommand;
use App\Collections\Schema\ShopRecommendationSchema;
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
        $setting = new ShopSettingSchema();
        $shopRecommendation = new ShopRecommendationSchema(
            [
                'expires_at' => Utils::refreshDay(),
                'refresh_count' => config('services.recommendation.refresh_limit') + 1,
            ],
        );

        $shop = ShopCollection::query()
            ->create([
                'domain' => $shopDomain,
            ]);
        $shop->settings()->create($setting->toArray());
        $shop->shopRecommendation()->create($shopRecommendation->toArray());
        return $shop;
    }
}
