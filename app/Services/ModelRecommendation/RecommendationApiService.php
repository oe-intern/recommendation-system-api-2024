<?php

namespace App\Services\ModelRecommendation;

use App\Contracts\ModelRecommendation\IRecommendationApi;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationApiService implements IRecommendationApi
{
    protected string $base_url;

    public function __construct()
    {
        $this->base_url = config('services.recommendation_url');
    }

    /**
     * Call the external recommend endpoint.
     *
     * @param array $data
     * @return array
     *
     * @throws Exception
     */
    public function recommend(array $data): array
    {
        try {
            $response = Http::post("$this->base_url/recommend", $data);

            return $response->json();
        } catch (Exception $e) {
            throw new Exception('Failed to call the external recommend endpoint.');
        }

    }

    /**
     * Call the external pre-recommend endpoint.
     *
     * @param array $data
     * @return array
     *
     * @throws Exception
     */
    public function preRecommend(array $data): array
    {
        try {
            $response = Http::post("$this->base_url/prerecommend", $data);

            return $response->json();
        } catch (Exception $e) {
            Log::log('error', 'Failed to call the external pre-recommend endpoint.', ['exception' => $e]);
            throw new Exception('Failed to call the external pre-recommend endpoint.');
        }
    }
}
