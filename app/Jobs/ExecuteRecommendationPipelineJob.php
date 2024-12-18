<?php


namespace App\Jobs;

use App\Collections\JobRecommendationCollection;
use App\Contracts\Commands\IJobRecommendationCommand;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\ModelRecommendation\IRecommendationApi;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProductRecommendation;
use App\Contracts\Recommendation\IRecommendationProcess;
use App\Contracts\Shopify\Graphql\Queries\IOrderQueryShopify;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\DTO\Payload\ShopProductRecommendationRequestDTO;
use App\Exceptions\ShopNotFoundException;
use App\Objects\Enums\JobRecommendationStatus;
use App\Objects\Transform\ProductTransform;
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
    private const MAX_RETRY_PROCESS = 20;

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
     * @var IProductQueryShopify
     */
    protected IProductQueryShopify $product_query_shopify;

    /**
     * @var IOrderQueryShopify
     */
    protected IOrderQueryShopify $order_query_shopify;

    /**
     * @var IRecommendationProcess
     */
    protected IRecommendationProcess $recommendation_process;

    /**
     * @var IProductQuery
     */
    protected IProductQuery $product_query;

    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * @var IShopCommand
     */
    protected IShopCommand $shop_command;

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
     * Create a new job instance.
     *
     * @param string $domain
     * @param array $products_data
     */
    public function __construct(string $domain, array $products_data)
    {
        $this->domain = $domain;
        $this->products_data = $products_data;
    }

    /**
     * Execute the job.
     * @throws ShopNotFoundException
     */
    public function handle(
        IProductQueryShopify $product_query_shopify,
        IOrderQueryShopify $order_query_shopify,
        IRecommendationProcess $recommendation_process,
        IProductQuery $product_query,
        IShopQuery $shop_query,
        IShopCommand $shop_command,
        IProductRecommendation $product_recommendation_service,
        IRecommendationApi $recommendation_api_service,
        IJobRecommendationCommand $job_recommendation_command,
        ProductTransform $product_transform,
    ): void {
        $this->initializeServices(
            $product_query_shopify,
            $order_query_shopify,
            $recommendation_process,
            $product_query,
            $shop_query,
            $shop_command,
            $product_recommendation_service,
            $recommendation_api_service,
            $job_recommendation_command,
        );

        $this->products_data = $product_transform->shopifyDataListToModelApiListData($this->products_data);
        $data_request = $this->getRequestData($this->domain);
        $map_gid_id = $this->getMapIdWithKeyGid($this->domain);
        $shop_id = $shop_query->getShopIdByDomain($this->domain);
        $retry = 0;

        $job = $this->createPendingJob($shop_id);

        do {
            try {
                $this->updateDefaultRecommendation($data_request, $map_gid_id);
                $this->updateJobStatus($job->getId(), JobRecommendationStatus::SUCCESS, []);

                ProcessShopInstalledData::dispatch($map_gid_id, $this->products_data, $shop_id, $data_request);
                return;
            } catch (Exception $e) {
                $retry++;
                sleep(self::DELAY_RECOMMEND_PROCESS);
            }
        } while ($retry < self::MAX_RETRY_PROCESS);

        $this->handleJobException($job->getId(), $e);
    }

    /**
     * @param IProductQueryShopify $product_query_shopify
     * @param IOrderQueryShopify $order_query_shopify
     * @param IRecommendationProcess $recommendation_process
     * @param IProductQuery $product_query
     * @param IShopQuery $shop_query
     * @param IShopCommand $shop_command
     * @param IProductRecommendation $product_recommendation_service
     * @param IRecommendationApi $recommendation_api_service
     * @param IJobRecommendationCommand $job_recommendation_command
     * @return void
     */
    private function initializeServices(
        IProductQueryShopify $product_query_shopify,
        IOrderQueryShopify $order_query_shopify,
        IRecommendationProcess $recommendation_process,
        IProductQuery $product_query,
        IShopQuery $shop_query,
        IShopCommand $shop_command,
        IProductRecommendation $product_recommendation_service,
        IRecommendationApi $recommendation_api_service,
        IJobRecommendationCommand $job_recommendation_command,
    ): void {
        $this->product_query_shopify = $product_query_shopify;
        $this->order_query_shopify = $order_query_shopify;
        $this->recommendation_process = $recommendation_process;
        $this->product_query = $product_query;
        $this->shop_query = $shop_query;
        $this->shop_command = $shop_command;
        $this->product_recommendation_service = $product_recommendation_service;
        $this->recommendation_api_service = $recommendation_api_service;
        $this->job_recommendation_command = $job_recommendation_command;
    }

    /**
     * Get request data for recommendation api
     *
     * @param string $domain
     * @return ShopProductRecommendationRequestDTO
     */
    private function getRequestData(string $domain): ShopProductRecommendationRequestDTO
    {
        $orders_process_data = $this->recommendation_process->processOrderData($domain);

        return $this->mergeData($this->products_data, $orders_process_data);
    }

    /**
     * Get data to request recommendation api
     *
     * @param array $products_data
     * @param array $orders_process_data
     * @return ShopProductRecommendationRequestDTO
     */
    private function mergeData(array $products_data, array $orders_process_data): ShopProductRecommendationRequestDTO
    {
        $total = data_get($orders_process_data, 'total');
        $type_scores = data_get($orders_process_data, 'type_scores');
        $product_scores = data_get($orders_process_data, 'product_scores');

        return new ShopProductRecommendationRequestDTO(
            self::MAX_RECOMMENDATION_PRODUCTS,
            $products_data,
            $type_scores,
            $total,
            $product_scores,
        );
    }

    /**
     * Get map id with key gid
     *
     * @param string $domain
     * @return array
     */
    private function getMapIdWithKeyGid(string $domain): array
    {
        $shop = $this->shop_query->getByDomain($domain);
        return $this->product_query->getMapIdWithKeyGidByShopId($shop->getId());
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
        $this->shop_command->updateLastJobRecommendation($shop_id, $job->getId());

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
     * @param int $job_id
     * @param Exception $e
     * @return void
     */
    private function handleJobException(int $job_id, Exception $e): void
    {
        $this->updateJobStatus($job_id, JobRecommendationStatus::FAILED, ['error' => $e->getMessage()]);
    }
}
