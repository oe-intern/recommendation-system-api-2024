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
    protected string $shop_id;

    /**
     * @var string
     */
    protected string $product_id;

    /**
     * @var mixed
     */
    protected mixed $data;

    /*
     * @var int
     */
    protected int $number_of_items;

    /**
     * @param string $shop_id
     * @param string $product_id
     * @param mixed $data
     * @param int|null $number_of_items
     */
    public function __construct(string $shop_id, string $product_id, mixed $data, ?int $number_of_items)
    {
        $this->shop_id = $shop_id;
        $this->product_id = $product_id;
        $this->data = $data;
        $this->number_of_items = $number_of_items;
    }

    /**
     * Execute the job
     *
     * @param IProductEvent $product_event_service
     * @param IProductQuery $product_query
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function handle(IProductEvent $product_event_service, IProductQuery $product_query): void
    {
        $product_event_service->addToCart(
            $this->shop_id,
            $this->product_id,
            $this->data,
            $this->number_of_items,
        );
    }
}
