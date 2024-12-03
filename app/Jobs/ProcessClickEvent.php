<?php

namespace App\Jobs;

use App\Contracts\Recommendation\IProductInteraction;
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
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function handle(IProductInteraction $product_interaction_service): void
    {
        $product_interaction_service->click(
            $this->shop_id,
            $this->product_id,
        );
    }
}
