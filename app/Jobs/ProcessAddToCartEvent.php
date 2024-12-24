<?php

namespace App\Jobs;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Recommendation\IProductEvent;
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
     * @var string
     */
    protected string $productId;

    /**
     * @var mixed
     */
    protected mixed $data;

    /*
     * @var int
     */
    protected int $numberOfItems;

    /**
     * @param string $shopId
     * @param string $productId
     * @param mixed $data
     * @param int|null $numberOfItems
     */
    public function __construct(string $shopId, string $productId, mixed $data, ?int $numberOfItems)
    {
        $this->shopId = $shopId;
        $this->productId = $productId;
        $this->data = $data;
        $this->numberOfItems = $numberOfItems;
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
            $this->productId,
            $this->data,
            $this->numberOfItems,
        );
    }
}
