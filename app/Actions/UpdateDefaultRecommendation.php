<?php

namespace App\Actions;

use App\Contracts\ModelRecommendation\IRecommendationApi;
use App\Contracts\Recommendation\IProductRecommendation;
use App\DTO\Payload\ShopProductRecommendationRequestDTO;
use Exception;
use Illuminate\Support\Facades\Log;

class UpdateDefaultRecommendation
{
    /**
     * @var IRecommendationApi
     */
    private IRecommendationApi $recommendationApiService;
    /**
     * @var IProductRecommendation
     */
    private IProductRecommendation $productRecommendationService;

    /**
     * UpdateDefaultRecommendation constructor.
     *
     * @param IRecommendationApi $recommendationApiService
     * @param IProductRecommendation $productRecommendationService
     */
    public function __construct(
        IRecommendationApi $recommendationApiService,
        IProductRecommendation $productRecommendationService,
    ) {
        $this->recommendationApiService = $recommendationApiService;
        $this->productRecommendationService = $productRecommendationService;
    }

    /**
     * Handle default recommendation update.
     *
     * @param ShopProductRecommendationRequestDTO $dataRequest
     * @param array $gidToIdMap
     * @return void
     * @throws Exception
     */
    public function __invoke(
        ShopProductRecommendationRequestDTO $dataRequest,
        array $gidToIdMap,
    ): void {
        try {
            $recommendationData = $this->recommendationApiService->preRecommend($dataRequest);
            $this->productRecommendationService->updateManyDefaultRecommendation($recommendationData, $gidToIdMap);
        } catch (Exception $e) {
            Log::error('Error updating default recommendation.', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
