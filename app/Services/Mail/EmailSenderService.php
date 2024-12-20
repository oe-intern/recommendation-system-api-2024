<?php

namespace App\Services\Mail;

use App\Collections\Schema\ShopRecommendationSchema;
use App\Contracts\Commands\IShopRecommendationCommand;
use App\Contracts\Mail\IEmailSender;
use App\Contracts\Queries\IShopRecommendationQuery;
use App\Contracts\Queries\User as IUserQuery;
use App\Mail\ProductRecommendationRefreshed;
use App\Objects\Enums\JobRecommendationStatus;
use App\Objects\Values\UserDomain;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailSenderService implements IEmailSender
{
    /**
     * @var IShopRecommendationQuery
     */
    protected IShopRecommendationQuery $shop_recommendation_query;

    /**
     * @var IShopRecommendationCommand
     */
    protected IShopRecommendationCommand $shop_recommendation_command;

    /**
     * @var IUserQuery
     */
    protected IUserQuery $user_query;

    /**
     * EmailSenderService constructor.
     *
     * @param IShopRecommendationQuery $shop_recommendation_query
     * @param IShopRecommendationCommand $shop_recommendation_command
     * @param IUserQuery $user_query
     */
    public function __construct(
        IShopRecommendationQuery $shop_recommendation_query,
        IShopRecommendationCommand $shop_recommendation_command,
        IUserQuery $user_query,
    ) {
        $this->shop_recommendation_query = $shop_recommendation_query;
        $this->shop_recommendation_command = $shop_recommendation_command;
        $this->user_query = $user_query;
    }

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
    ): void {
        $shop_recommendation = $this->shop_recommendation_query->getByShopId($shop_id);
        if (!$shop_recommendation->getEmailNotification()) {
            return;
        }

        $email = $this->getShopEmail($shop_recommendation, $shop_id, $shop_domain);

        Log::info('Sending email to ' . $email);
        Mail::to($email)->queue(
            new ProductRecommendationRefreshed(
                $this->getShopName($shop_domain),
                $status,
                $email,
            ),
        );
    }

    /**
     * Get shop email & update if not exist.
     *
     * @param ShopRecommendationSchema $shop_recommendation
     * @param string $shop_id
     * @param string $domain
     * @return string
     */
    private function getShopEmail(
        ShopRecommendationSchema $shop_recommendation,
        string $shop_id,
        string $domain,
    ): string {
        $email = $shop_recommendation->getEmail();

        if (!$email) {
            $email = $this->updateEmail($shop_id, $domain);
        }

        return $email;
    }

    /**
     * Update email for shop recommendation.
     *
     * @param string $shop_id
     * @param string $domain
     * @return string
     */
    private function updateEmail(string $shop_id, string $domain): string
    {
        $user = $this->user_query->getByDomain(UserDomain::fromNative($domain));
        $email = $user->getShopEmail();
        $this->shop_recommendation_command->updateNotification(
            $shop_id,
            true,
            $email,
        );

        return $email;
    }

    /**
     * Get shop name from shop domain.
     *
     * @param string $shop_domain
     * @return string
     */
    private function getShopName(string $shop_domain): string
    {
        return explode('.', $shop_domain)[0];
    }
}
