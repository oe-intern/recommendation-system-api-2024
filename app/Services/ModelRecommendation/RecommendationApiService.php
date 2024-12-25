<?php

namespace App\Services\ModelRecommendation;

use App\Contracts\ModelRecommendation\IRecommendationApi;
use App\DTO\Payload\ProductRecommendationRequestDTO;
use App\DTO\Payload\ShopProductRecommendationRequestDTO;
use App\DTO\Service\GetJobRecommendationResponse;
use App\DTO\Service\JobRecommendationResponse;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RecommendationApiService implements IRecommendationApi
{
    /**
     * Endpoints for the recommendation API.
     */
    private const ENDPOINT_RECOMMEND = 'recommendation';

    /**
     * Endpoints for the pre-recommendation API.
     */
    private const ENDPOINT_PRE_RECOMMEND = 'pre-recommendation';

    /**
     * Endpoints for the product recommendation API.
     */
    private const ENDPOINT_PRODUCT_RECOMMEND = 'product';
    /**
     * @var string
     */
    protected string $baseUrl;
    /**
     * Max retry attempts for the recommendation API.
     *
     * @var int
     */
    private int $maxRetries; // seconds
/**
     * Timeouts for the recommendation API.
     *
     * @var int
     */
    private int $timeout;

    /**
     * RecommendationApiService constructor.
     */
    public function __construct()
    {
        $this->baseUrl = config('services.recommendation.url');
        $this->maxRetries = config('services.recommendation.max_retries');
        $this->timeout = config('services.recommendation.timeout');
    }

    /**
     * Call the external recommend endpoint.
     *
     * @param ShopProductRecommendationRequestDTO $data
     * @return JobRecommendationResponse
     * @throws Exception
     */
    public function recommend(ShopProductRecommendationRequestDTO $data): JobRecommendationResponse
    {
        $response = $this->makePostRequest(self::ENDPOINT_RECOMMEND, $data->toArray());

        return new JobRecommendationResponse($response['job_id'], $response['status']);
    }

    /**
     * Call the external pre-recommend endpoint.
     *
     * @param ShopProductRecommendationRequestDTO $data
     * @return array
     * @throws Exception
     */
    public function preRecommend(ShopProductRecommendationRequestDTO $data): array
    {
        return $this->makePostRequest(self::ENDPOINT_PRE_RECOMMEND, $data->toArray());
    }

    /**
     * Call the external recommend for 1 product endpoint.
     *
     * @param ProductRecommendationRequestDTO $data
     * @return array
     * @throws Exception
     */
    public function recommendProduct(ProductRecommendationRequestDTO $data): array
    {
        return $this->makePostRequest(self::ENDPOINT_PRODUCT_RECOMMEND, $data->toArray());
    }

    /**
     * Make a POST request to the recommendation API.
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     *
     * @throws Exception
     */
    private function makePostRequest(string $endpoint, array $data): array
    {
        try {
            $response = $this->getHttpRequest()->post("$this->baseUrl/$endpoint", $data);
            return $this->parseResponse($response, $endpoint);
        } catch (Exception $e) {
            throw new Exception("Failed to call the $endpoint endpoint.");
        }
    }

    /**
     * Get a new HTTP request instance.
     *
     * @return PendingRequest
     */
    private function getHttpRequest(): PendingRequest
    {
        return Http::timeout($this->timeout)->retry($this->maxRetries);
    }

    /**
     * Handle the response from the recommendation API.
     *
     * @param $response
     * @param string $endpoint
     * @return mixed
     * @throws Exception
     */
    private function parseResponse($response, string $endpoint): array
    {
        if ($response->getStatusCode() !== 200) {
            throw new Exception("Error in API call to endpoint: $endpoint");
        }

        $responseData = $response->json();
        if (!isset($responseData['data'])) {
            throw new Exception("Invalid response structure from $endpoint.");
        }

        return $responseData['data'];
    }

    /**
     * Call the external check state of task recommendation endpoint.
     *
     * @param string $jobId
     * @return GetJobRecommendationResponse
     * @throws Exception
     */
    public function getJobRecommendation(string $jobId): GetJobRecommendationResponse
    {
        $response = $this->makeGetRequest("job/$jobId");

        return new GetJobRecommendationResponse(
            $response['job_id'],
            $response['status'],
            $response['result_url'] ?? '',
            null,
        );
    }

    /**
     * Make a GET request to the recommendation API.
     *
     * @param string $endpoint
     * @return array
     *
     * @throws Exception
     */
    private function makeGetRequest(string $endpoint): array
    {
        try {
            $response = $this->getHttpRequest()->get("$this->baseUrl/$endpoint");
            return $this->parseResponse($response, $endpoint);
        } catch (Exception $e) {
            throw new Exception("Failed to call the $endpoint endpoint.");
        }
    }

    /**
     * Call the external recommend for check state of task recommendation endpoint.
     *
     * @param string $url
     * @return array
     *
     * @throws Exception
     */
    public function getJobRecommendationResult(string $url): array
    {
        $response = $this->getHttpRequest()->get($url);

        if ($response->getStatusCode() !== 200) {
            throw new Exception("Failed to fetch job recommendation result.");
        }

        return $response->json();
    }
}
