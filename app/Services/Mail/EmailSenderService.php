<?php

namespace App\Services\Mail;

use App\Collections\Schema\ShopRecommendationSchema;
use App\Contracts\Commands\IShopRecommendationCommand;
use App\Contracts\Mail\IEmailSender;
use App\Contracts\Queries\IShopRecommendationQuery;
use App\Contracts\Queries\User as IUserQuery;
use App\Jobs\SendEmailJob;
use App\Mail\ProductRecommendationRefreshed;
use App\Objects\Enums\JobRecommendationStatus;
use App\Objects\Values\UserDomain;

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
     * Email queue name.
     */
    private string $emailQueue;

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
        $this->emailQueue = config('queue.queues.email');
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

        if (!$this->shouldSendEmail($shopRecommendation)) {
            return;
        }

        $email = $this->getOrUpdateShopEmail($shopRecommendation, $shopId, $shopDomain);

        $this->sendEmail($email, $shopDomain, $status);
    }

    /**
     * Determine if email notification should be sent.
     *
     * @param ShopRecommendationSchema $shopRecommendation
     * @return bool
     */
    private function shouldSendEmail(ShopRecommendationSchema $shopRecommendation): bool
    {
        return $shopRecommendation->getEmailNotification();
    }

    /**
     * Get shop email & update if not exist.
     *
     * @param ShopRecommendationSchema $shopRecommendation
     * @param string $shopId
     * @param string $domain
     * @return string
     */
    private function getOrUpdateShopEmail(
        ShopRecommendationSchema $shopRecommendation,
        string $shopId,
        string $domain,
    ): string {
        $email = $shopRecommendation->getEmail();

        if (!$email) {
            $email = $this->updateShopEmail($shopId, $domain);
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
    private function updateShopEmail(string $shopId, string $domain): string
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
     * Send email to the shop admin.
     *
     * @param string $email
     * @param string $shopDomain
     * @param JobRecommendationStatus $status
     * @return void
     */
    private function sendEmail(string $email, string $shopDomain, JobRecommendationStatus $status): void
    {
        $shopName = $this->extractShopNameFromDomain($shopDomain);

        $mailable = new ProductRecommendationRefreshed(
            $shopName,
            $status,
            $email,
        );

        SendEmailJob::dispatch($mailable)->onQueue($this->emailQueue);
    }

    /**
     * Get shop name from shop domain.
     *
     * @param string $shopDomain
     * @return string
     */
    private function extractShopNameFromDomain(string $shopDomain): string
    {
        return explode('.', $shopDomain)[0];
    }
}
