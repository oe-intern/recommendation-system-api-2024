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
    protected string $shop_id;

    /**
     * @var string
     */
    protected string $product_id;

    /**
     * @var mixed
     */
    protected mixed $data;

    /**
     * @param string $shop_id
     * @param string $product_id
     * @param mixed $data
     */
    public function __construct(string $shop_id, string $product_id, mixed $data)
    {
        $this->shop_id = $shop_id;
        $this->product_id = $product_id;
        $this->data = $data;
    }

    /**
     * Execute the job
     *
     * @param IProductEvent $product_event_service
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function handle(IProductEvent $product_event_service): void
    {
        $product_event_service->click(
            $this->shop_id,
            $this->product_id,
            $this->data
        );
    }
}
