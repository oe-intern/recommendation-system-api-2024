<?php

namespace App\Contracts\ModelRecommendation;

use Exception;

interface IRecommendationApi
{
    /**
     * Call the external recommend endpoint.
     *
     * @param array $data
     * @return array
     *
     * @throws Exception
     */
    public function recommend(array $data): array;

    /**
     * Call the external pre-recommend endpoint.
     *
     * @param array $data
     * @return array
     *
     * @throws Exception
     */
    public function preRecommend(array $data): array;
}
