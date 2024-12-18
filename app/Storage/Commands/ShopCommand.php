<?php

namespace App\Storage\Commands;

use App\Collections\Schema\ShopSettingSchema;
use App\Collections\ShopCollection;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\Queries\IShopQuery;
use App\Collections\Schema\ShopRecommendationSchema;
use App\Objects\Enums\RecommendationState;
use App\Lib\Utils;

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
        $shop_recommendation = new ShopRecommendationSchema(
            [
                'expires_at' => Utils::refreshDay(),
                'refresh_count' => config('services.recommendation.refresh_count') + 1,
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
        $shop = $this->shop_query->getById($shop_id);
        $shop?->shopRecommendation()->update([
            'last_job_recommendation_id' => $job_recommendation_id,
        ]);
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

        $shop?->settings()->update([
            'auto_recommendation' => $state,
        ]);
    }
}
