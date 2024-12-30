<?php

namespace App\DTO\Response;

use App\Collections\Schema\ShopRecommendationSchema;
use Illuminate\Contracts\Support\Arrayable;

readonly class GetShopRecommendationResponse implements Arrayable
{
    public function __construct(
        private ShopRecommendationSchema $shopRecommendationSchema,
    ) {}

    public function toArray(): array
    {
        return [
            'refresh_count' => $this->shopRecommendationSchema->getRefreshCount(),
            'expires_at' => $this->shopRecommendationSchema->getExpiresAt(),
            'email' => $this->shopRecommendationSchema->getEmail(),
            'email_notification' => $this->shopRecommendationSchema->getEmailNotification(),
        ];
    }
}
