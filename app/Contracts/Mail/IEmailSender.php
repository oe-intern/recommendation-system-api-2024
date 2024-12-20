<?php

namespace App\Contracts\Mail;

use App\Objects\Enums\JobRecommendationStatus;

interface IEmailSender
{
    /**
     * Handle send email to admin after complete recommendation process.
     *
     * @param string $shop_id
     * @param string $shop_domain
     * @param JobRecommendationStatus $status
     * @return void
     */
    public function sendRecommendationEmail(
        string $shop_id,
        string $shop_domain,
        JobRecommendationStatus $status,
    ): void;
}
