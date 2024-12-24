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
    protected IShopRecommendationQuery $shopRecommendationQuery;

    /**
     * @var IShopRecommendationCommand
     */
    protected IShopRecommendationCommand $shopRecommendationCommand;

    /**
     * @var IUserQuery
     */
    protected IUserQuery $userQuery;

    /**
     * EmailSenderService constructor.
     *
     * @param IShopRecommendationQuery $shopRecommendationQuery
     * @param IShopRecommendationCommand $shopRecommendationCommand
     * @param IUserQuery $userQuery
     */
    public function __construct(
        IShopRecommendationQuery $shopRecommendationQuery,
        IShopRecommendationCommand $shopRecommendationCommand,
        IUserQuery $userQuery,
    ) {
        $this->shopRecommendationQuery = $shopRecommendationQuery;
        $this->shopRecommendationCommand = $shopRecommendationCommand;
        $this->userQuery = $userQuery;
    }

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
    ): void {
        $shopRecommendation = $this->shopRecommendationQuery->getByShopId($shopId);
        if (!$shopRecommendation->getEmailNotification()) {
            return;
        }

        $email = $this->getShopEmail($shopRecommendation, $shopId, $shopDomain);

        Log::info('Sending email to ' . $email. ' for shop ' . $shopDomain);
        Mail::to($email)->queue(
            new ProductRecommendationRefreshed(
                $this->getShopName($shopDomain),
                $status,
                $email,
            ),
        );
    }

    /**
     * Get shop email & update if not exist.
     *
     * @param ShopRecommendationSchema $shopRecommendation
     * @param string $shopId
     * @param string $domain
     * @return string
     */
    private function getShopEmail(
        ShopRecommendationSchema $shopRecommendation,
        string $shopId,
        string $domain,
    ): string {
        $email = $shopRecommendation->getEmail();

        if (!$email) {
            $email = $this->updateEmail($shopId, $domain);
        }

        return $email;
    }

    /**
     * Update email for shop recommendation.
     *
     * @param string $shopId
     * @param string $domain
     * @return string
     */
    private function updateEmail(string $shopId, string $domain): string
    {
        $user = $this->userQuery->getByDomain(UserDomain::fromNative($domain));
        $email = $user->getShopEmail();
        $this->shopRecommendationCommand->updateNotification(
            $shopId,
            true,
            $email,
        );

        return $email;
    }

    /**
     * Get shop name from shop domain.
     *
     * @param string $shopDomain
     * @return string
     */
    private function getShopName(string $shopDomain): string
    {
        return explode('.', $shopDomain)[0];
    }
}
