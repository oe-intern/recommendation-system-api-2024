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
    protected IShopQuery $shopQuery;

    /**
     * ShopCommand constructor.
     *
     * @param IShopQuery $shopQuery
     */
    public function __construct(IShopQuery $shopQuery)
    {
        $this->shopQuery = $shopQuery;
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
        $shopSettings = $shop->settings()->get();

        if (!$shopSettings) {
            $shopSettings = $shop->settings()->create($settings);
        } else {
            $shopSettings->fill($settings);
            $shopSettings->save();
        }

        return $shopSettings->toArray();
    }

    /**
     * Set the recommendation state for a shop.
     *
     * @param string $shopId
     * @param RecommendationState $state
     * @return bool
     */
    public function setRecommendationState(string $shopId, RecommendationState $state): bool
    {
        $shop = $this->shopQuery->getById($shopId);

        return $shop?->settings()->get()->update([
            'auto_recommendation' => $state,
        ]);
    }
}
