<?php

namespace App\DTO\Response;

use App\Collections\Schema\ShopRecommendationSchema;
use Illuminate\Contracts\Support\Arrayable;

readonly class UpdateShopRecommendationNotificationResponse implements Arrayable
{
    /**
     * UpdateShopRecommendationNotificationResponse constructor.
     *
     * @param ShopRecommendationSchema $shopRecommendation
     */
    public function __construct(
        private ShopRecommendationSchema $shopRecommendation,
    ) {}

    /**
     * Convert data to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'email' => $this->shopRecommendation->getEmail(),
            'email_notification' => $this->shopRecommendation->getEmailNotification(),
        ];
    }
}
