<?php


namespace App\Jobs;

use App\Collections\JobRecommendationCollection;
use App\Contracts\Commands\IJobRecommendationCommand;
use App\Contracts\Commands\IShopRecommendationCommand;
use App\Contracts\Mail\IEmailSender;
use App\Contracts\ModelRecommendation\IRecommendationApi;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\DTO\Payload\ShopProductRecommendationRequestDTO;
use App\Objects\Enums\JobRecommendationStatus;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ExecuteRecommendationPipelineJob implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * @var int
     */
    private const MAX_RECOMMENDATION_PRODUCTS = 6;

    /**
     * @var int
     */
    private const MAX_RETRY_PROCESS = 5;

    /**
     * @var int
     */
    private const DELAY_RECOMMEND_PROCESS = 20;

    /**
     * @var string
     */
    protected string $domain;

    /**
     * @var array
     */
    protected array $products_data;

    /**
     * @var array
     */
    protected array $orders_data;

    /**
     * @var IProductQuery
     */
    protected IProductQuery $product_query;

    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * @var IShopRecommendationCommand
     */
    protected IShopRecommendationCommand $shop_recommendation_command;

    /**
     * @var IProductRecommendation
     */
    protected IProductRecommendation $product_recommendation_service;

    /**
     * @var IRecommendationApi
     */
    protected IRecommendationApi $recommendation_api_service;

    /**
     * @var IJobRecommendationCommand
     */
    protected IJobRecommendationCommand $job_recommendation_command;

    /**
     * @var IEmailSender
     */
    protected IEmailSender $email_sender_service;

    /**
     * Create a new job instance.
     *
     * @param string $domain
     * @param array $products_data
     * @param array $orders_data
     */
    public function __construct(string $domain, array $products_data, array $orders_data)
    {
        $this->domain = $domain;
        $this->products_data = $products_data;
        $this->orders_data = $orders_data;
    }

    /**
     * Execute the job.
     *
     * @param IProductQuery $product_query
     * @param IShopQuery $shop_query
     * @param IShopRecommendationCommand $shop_recommendation_command
     * @param IProductRecommendation $product_recommendation_service
     * @param IRecommendationApi $recommendation_api_service
     * @param IJobRecommendationCommand $job_recommendation_command
     * @param IEmailSender $email_sender_service
     */
    public function handle(
        IProductQuery $product_query,
        IShopQuery $shop_query,
        IShopRecommendationCommand $shop_recommendation_command,
        IProductRecommendation $product_recommendation_service,
        IRecommendationApi $recommendation_api_service,
        IJobRecommendationCommand $job_recommendation_command,
        IEmailSender $email_sender_service,
    ): void {
        $this->initializeServices(
            $product_query,
            $shop_query,
            $shop_recommendation_command,
            $product_recommendation_service,
            $recommendation_api_service,
            $job_recommendation_command,
            $email_sender_service,
        );

        $shop = $shop_query->getByDomain($this->domain);
        $shop_id = $shop->getId();
        $data_request = $this->getRequestData($this->domain);
        $map_gid_id = $this->getMapIdWithKeyGid($shop_id);
        $retry = 0;

        $job = $this->createPendingJob($shop_id);

        do {
            try {
                $this->updateDefaultRecommendation($data_request, $map_gid_id);
                $this->updateJobStatus($job->getId(), JobRecommendationStatus::SUCCESS, []);

                ProcessShopInstalledData::dispatch(
                    $map_gid_id,
                    $this->products_data,
                    $shop_id,
                    $this->domain,
                    $data_request
                );
                return;
            } catch (Exception $e) {
                $retry++;
                sleep(self::DELAY_RECOMMEND_PROCESS);
            }
        } while ($retry < self::MAX_RETRY_PROCESS);

        $this->handleJobException($job->getId(), $shop_id, $this->domain);
    }

    /**
     * Initialize services.
     *
     * @param IProductQuery $product_query
     * @param IShopQuery $shop_query
     * @param IShopRecommendationCommand $shop_recommendation_command
     * @param IProductRecommendation $product_recommendation_service
     * @param IRecommendationApi $recommendation_api_service
     * @param IJobRecommendationCommand $job_recommendation_command
     * @param IEmailSender $email_sender_service
     * @return void
     */
    private function initializeServices(
        IProductQuery $product_query,
        IShopQuery $shop_query,
        IShopRecommendationCommand $shop_recommendation_command,
        IProductRecommendation $product_recommendation_service,
        IRecommendationApi $recommendation_api_service,
        IJobRecommendationCommand $job_recommendation_command,
        IEmailSender $email_sender_service,
    ): void {
        $this->product_query = $product_query;
        $this->shop_query = $shop_query;
        $this->shop_recommendation_command = $shop_recommendation_command;
        $this->product_recommendation_service = $product_recommendation_service;
        $this->recommendation_api_service = $recommendation_api_service;
        $this->job_recommendation_command = $job_recommendation_command;
        $this->email_sender_service = $email_sender_service;
    }

    /**
     * Get request data for recommendation api
     *
     * @param string $domain
     * @return ShopProductRecommendationRequestDTO
     */
    private function getRequestData(string $domain): ShopProductRecommendationRequestDTO
    {
        return $this->mergeData();
    }

    /**
     * Get data to request recommendation api
     *
     * @return ShopProductRecommendationRequestDTO
     */
    private function mergeData(): ShopProductRecommendationRequestDTO
    {
        $total = data_get($this->orders_data, 'total');
        $type_scores = data_get($this->orders_data, 'type_scores');
        $product_scores = data_get($this->orders_data, 'product_scores');

        return new ShopProductRecommendationRequestDTO(
            self::MAX_RECOMMENDATION_PRODUCTS,
            $this->products_data,
            $type_scores,
            $total,
            $product_scores,
        );
    }

    /**
     * Get map id with key gid
     *
     * @param string $shop_id
     * @return array
     */
    private function getMapIdWithKeyGid(string $shop_id): array
    {
        return $this->product_query->getMapIdWithKeyGidByShopId($shop_id);
    }

    /**
     * Create pending job.
     *
     * @param string $shop_id
     * @return JobRecommendationCollection
     */
    private function createPendingJob(string $shop_id): JobRecommendationCollection
    {
        $job = $this->job_recommendation_command->create(
            $shop_id,
            JobRecommendationStatus::PENDING,
            0,
        );
        $this->shop_recommendation_command->updateLastJobRecommendation($shop_id, $job->getId());

        return $job;
    }

    /**
     * Update default recommendation
     *
     * @param ShopProductRecommendationRequestDTO $data_request
     * @param array $map_gid_id
     * @return void
     * @throws Exception
     */
    private function updateDefaultRecommendation(
        ShopProductRecommendationRequestDTO $data_request,
        array $map_gid_id,
    ): void {
        try {
            $recommendation_data = $this->recommendation_api_service->preRecommend($data_request);
            $this->product_recommendation_service->updateManyDefaultRecommendation($recommendation_data, $map_gid_id);
        } catch (Exception $e) {
            Log::error('Error updating default recommendation.', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update job recommendation status.
     *
     * @param string $job_id
     * @param JobRecommendationStatus $status
     * @param array $data
     * @return void
     */
    private function updateJobStatus(string $job_id, JobRecommendationStatus $status, array $data): void
    {
        $this->job_recommendation_command->update($job_id, $status, $data);
    }

    /**
     * Handle exceptions during job processing.
     *
     * @param string $job_id
     * @param string $shop_id
     * @param string $shop_domain
     * @return void
     */
    private function handleJobException(string $job_id, string $shop_id, string $shop_domain): void
    {
        $this->updateJobStatus($job_id, JobRecommendationStatus::FAILED, ['error' => 'Error processing job.']);
        $this->sendEmail(JobRecommendationStatus::FAILED, $shop_id, $shop_domain);
    }

    /**
     * Send email notification.
     *
     * @param JobRecommendationStatus $status
     * @param string $shop_id
     * @param string $shop_domain
     * @return void
     */
    private function sendEmail(JobRecommendationStatus $status, string $shop_id, string $shop_domain): void
    {
        $this->email_sender_service->sendRecommendationEmail($shop_id, $shop_domain, $status);
    }
}
