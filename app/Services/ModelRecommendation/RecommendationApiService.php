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
     * Max retry attempts for the recommendation API.
     *
     * @var int
     */
    private int $MAX_RETRIES;

    /**
     * Timeouts for the recommendation API.
     *
     * @var int
     */
    private int $TIMEOUT; // seconds

    /**
     * @var string
     */
    protected string $baseUrl;

    /**
     * RecommendationApiService constructor.
     */
    public function __construct()
    {
        $this->baseUrl = config('services.recommendation.url');
        $this->MAX_RETRIES = config('services.recommendation.max_retries');
        $this->TIMEOUT = config('services.recommendation.timeout');
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
        $response = $this->makePostRequest('recommendation', $data->toArray());

        return new JobRecommendationResponse($response['job_id'], $response['status']);
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
            return $this->handleResponse($response);
        } catch (Exception $e) {
            throw new Exception("Failed to call the $endpoint endpoint.");
        }
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
        return $this->makePostRequest('pre-recommendation', $data->toArray());
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
        return $this->makePostRequest('product', $data->toArray());
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
            null
        );
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
        try {
            $response = $this->getHttpRequest()->get($url);
            if ($response->getStatusCode() !== 200) {
                throw new Exception("Failed to call the getJobRecommendationResult endpoint.");
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Failed to call the getJobRecommendationResult endpoint.");
        }
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
            return $this->handleResponse($response);
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
        return Http::timeout($this->TIMEOUT)->retry($this->MAX_RETRIES);
    }

    /**
     * Handle the response from the recommendation API.
     *
     * @param $response
     * @return mixed
     * @throws Exception
     */
    private function handleResponse($response): array
    {
        if ($response->getStatusCode() !== 200) {
            throw new Exception("Failed to call the recommendation API.");
        }

        $responseData = $response->json();
        if (!isset($responseData['data'])) {
            throw new Exception("Unexpected response structure from getJobRecommendation.");
        }

        return $responseData['data'];
    }
}
