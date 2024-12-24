<?php

namespace App\Contracts\ModelRecommendation;

use App\DTO\Payload\ProductRecommendationRequestDTO;
use App\DTO\Payload\ShopProductRecommendationRequestDTO;
use App\DTO\Service\GetJobRecommendationResponse;
use App\DTO\Service\JobRecommendationResponse;
use Exception;

interface IRecommendationApi
{
    /**
     * Call the external recommend endpoint.
     *
     * @param ShopProductRecommendationRequestDTO $data
     * @return JobRecommendationResponse
     *
     * @throws Exception
     */
    public function recommend(ShopProductRecommendationRequestDTO $data): JobRecommendationResponse;

    /**
     * Call the external pre-recommend endpoint.
     *
     * @param ShopProductRecommendationRequestDTO $data
     * @return array
     *
     * @throws Exception
     */
    public function preRecommend(ShopProductRecommendationRequestDTO $data): array;

    /**
     * Call the external recommend for 1 product endpoint.
     *
     * @param ProductRecommendationRequestDTO $data
     * @return array
     *
     * @throws Exception
     */
    public function recommendProduct(ProductRecommendationRequestDTO $data): array;

    /**
     * Call the external recommend for check state of task recommendation endpoint.
     *
     * @param string $jobId
     * @return GetJobRecommendationResponse
     *
     * @throws Exception
     */
    public function getJobRecommendation(string $jobId): GetJobRecommendationResponse;

    /**
     * Call the external recommend for check state of task recommendation endpoint.
     *
     * @param string $url
     * @return array
     *
     * @throws Exception
     */
    public function getJobRecommendationResult(string $url): array;
}
