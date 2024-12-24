<?php

namespace App\Contracts\Mail;

use App\Objects\Enums\JobRecommendationStatus;

interface IEmailSender
{
    /**
     * Handle send email to admin after complete recommendation process.
     *
     * @param string $shopId
     * @param string $shopDomain
     * @param JobRecommendationStatus $status
     * @return void
     */
    public function sendRecommendationEmail(
        string $shopId,
        string $shopDomain,
        JobRecommendationStatus $status,
    ): void;
}
