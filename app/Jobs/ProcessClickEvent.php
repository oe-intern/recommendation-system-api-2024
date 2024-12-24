<?php

namespace App\Jobs;

use App\Contracts\Recommendation\IProductEvent;
use App\Exceptions\ProductNotFoundException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessClickEvent implements ShouldQueue
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

    /**
     * @param string $shopId
     * @param string $productId
     * @param mixed $data
     */
    public function __construct(string $shopId, string $productId, mixed $data)
    {
        $this->shopId = $shopId;
        $this->productId = $productId;
        $this->data = $data;
    }

    /**
     * Execute the job
     *
     * @param IProductEvent $productEventService
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function handle(IProductEvent $productEventService): void
    {
        $productEventService->click(
            $this->shopId,
            $this->productId,
            $this->data
        );
    }
}
