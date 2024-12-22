<?php

namespace App\Storage\Commands;

use App\Collections\ShopCollection;
use App\Contracts\Commands\IShopSettingCommand;
use App\Contracts\Queries\IShopQuery;
use App\Objects\Enums\RecommendationState;

class ShopSettingCommand implements IShopSettingCommand
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
     * @return bool
     */
    public function setRecommendationState(string $shop_id, RecommendationState $state): bool
    {
        $shop = $this->shop_query->getById($shop_id);

        return $shop?->settings()->get()->update([
            'auto_recommendation' => $state,
        ]);
    }
}
