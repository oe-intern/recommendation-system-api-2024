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
     * @param string $shop_domain
     * @return ShopCollection
     */
    public function create(string $shop_domain): ShopCollection
    {
        $setting = new ShopSettingSchema();
        $shop_recommendation = new ShopRecommendationSchema(
            [
                'expires_at' => Utils::refreshDay(),
                'refresh_count' => config('services.recommendation.refresh_limit') + 1,
            ],
        );

        $shop = ShopCollection::query()
            ->create([
                'domain' => $shop_domain,
            ]);
        $shop->settings()->create($setting->toArray());
        $shop->shopRecommendation()->create($shop_recommendation->toArray());
        return $shop;
    }
}
