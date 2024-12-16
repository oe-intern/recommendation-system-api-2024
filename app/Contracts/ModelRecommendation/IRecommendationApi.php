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

    /**
     * Call the external recommend for 1 product endpoint.
     *
     * @param array $data
     * @return array
     *
     * @throws Exception
     */
    public function recommendProduct(array $data): array;

    /**
     * Call the external recommend for check state of task recommendation endpoint.
     *
     * @param array $data
     * @return array
     *
     * @throws Exception
     */
    public function checkStateTaskRecommendation(array $data): array;
}
