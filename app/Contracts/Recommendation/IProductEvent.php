<?php

namespace App\Contracts\Recommendation;

use App\DTO\Request\AddToCartEventRequestDTO;
use App\DTO\Request\ClickEventRequestDTO;
use App\DTO\Request\GetEventAnalyticRequestDTO;
use App\DTO\Request\GetProductPerformanceRequestDTO;
use App\Exceptions\ProductNotFoundException;

interface IProductEvent
{
    /**
     * Handle click event.
     *
     * @param string $shopId
     * @param ClickEventRequestDTO $requestDTO
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function click(string $shopId, ClickEventRequestDTO $requestDTO): void;

    /**
     * Handle add to cart event.
     *
     * @param string $shopId
     * @param AddToCartEventRequestDTO $requestDTO
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function addToCart(string $shopId, AddToCartEventRequestDTO $requestDTO): void;

    /**
     * Get event analytic.
     *
     * @param string $shopId
     * @param GetProductPerformanceRequestDTO $requestDTO
     * @return array
     */
    public function getProductPerformance(string $shopId, GetProductPerformanceRequestDTO $requestDTO): array;

    /**
     * Get click data for a product.
     *
     * @param string $shopId
     * @param GetEventAnalyticRequestDTO $requestDTO
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getClickData(
        string $shopId,
        GetEventAnalyticRequestDTO $requestDTO,
    ): array;

    /**
     * Get add to cart data for a product.
     *
     * @param string $shopId
     * @param GetEventAnalyticRequestDTO $requestDTO
     * @return array
     *
     * @throws ProductNotFoundException
     */
    public function getAddToCartData(
        string $shopId,
        GetEventAnalyticRequestDTO $requestDTO,
    ): array;


}
