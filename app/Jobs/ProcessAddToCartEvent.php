<?php

namespace App\Jobs;

use App\Contracts\Queries\IProductQuery;
use App\Contracts\Recommendation\IProductInteraction;
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
     * @var array
     */
    protected array $data;

    /**
     * @param string $shop_id
     * @param string $product_id
     * @param array $data
     */
    public function __construct(string $shop_id, string $product_id, array $data)
    {
        $this->shop_id = $shop_id;
        $this->product_id = $product_id;
        $this->data = $data;
    }

    /**
     * Execute the job
     *
     * @param IProductInteraction $product_interaction_service
     * @param IProductQuery $product_query
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function handle(IProductInteraction $product_interaction_service, IProductQuery $product_query): void
    {
        $number_of_interactions = data_get($this->data, 'number_of_interactions');

        $product_interaction_service->addToCart(
            $this->shop_id,
            $this->product_id,
            $number_of_interactions,
        );
    }
}
