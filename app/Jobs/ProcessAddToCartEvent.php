<?php

namespace App\Jobs;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Recommendation\IProductEvent;
use App\DTO\Request\AddToCartEventRequestDTO;
use App\Exceptions\ProductNotFoundException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAddToCartEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected string $shopId;

    /**
     * @var AddToCartEventRequestDTO
     */
    protected AddToCartEventRequestDTO $addToCartEventRequestDTO;

    /**
     * @param string $shopId
     * @param AddToCartEventRequestDTO $addToCartEventRequestDTO
     */
    public function __construct(string $shopId, AddToCartEventRequestDTO $addToCartEventRequestDTO)
    {
        $this->shopId = $shopId;
        $this->addToCartEventRequestDTO = $addToCartEventRequestDTO;
    }

    /**
     * Execute the job
     *
     * @param IProductEvent $productEventService
     * @param IProductQuery $productQuery
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function handle(IProductEvent $productEventService, IProductQuery $productQuery): void
    {
        $productEventService->addToCart(
            $this->shopId,
            $this->addToCartEventRequestDTO,
        );
    }
}
