<?php

namespace App\Jobs;

use App\Contracts\Commands\IProductCommand;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Queries\IProductQuery;
use App\Objects\Transform\ProductTransform;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateProduct implements ShouldQueue
{
    use Queueable, Dispatchable;


    /**
     * @var string
     */
    protected string $shop_id;

    /**
     * @var array
     */
    protected array $product;

    /**
     * Create a new job instance.
     *
     * @param string $shop_id
     * @param array $product
     */
    public function __construct(string $shop_id, array $product)
    {
        $this->shop_id = $shop_id;
        $this->product = $product;
    }


    /**
     * Execute the job.
     *
     * @param IProductCommand $product_command
     * @param IShopQuery $shop_query
     * @param ProductTransform $product_transform
     * @param IProductQuery $product_query
     * @return void
     */
    public function handle(IProductCommand $product_command, IShopQuery $shop_query, ProductTransform $product_transform, IProductQuery $product_query): void
    {
        $product = $product_query->getByShopIdAndGid($this->shop_id, $this->product['id']);
        if ($product) {
            $product_data = $product_transform->webhookDataToCollectionData($this->product);
            $product_command->update($product, $product_data);
        }
    }
}
