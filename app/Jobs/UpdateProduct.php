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
    protected string $shopId;

    /**
     * @var array
     */
    protected array $product;

    /**
     * Create a new job instance.
     *
     * @param string $shopId
     * @param array $product
     */
    public function __construct(string $shopId, array $product)
    {
        $this->shopId = $shopId;
        $this->product = $product;
    }


    /**
     * Execute the job.
     *
     * @param IProductCommand $productCommand
     * @param IShopQuery $shopQuery
     * @param ProductTransform $productTransform
     * @param IProductQuery $productQuery
     * @return void
     */
    public function handle(IProductCommand $productCommand, IShopQuery $shopQuery, ProductTransform $productTransform, IProductQuery $productQuery): void
    {
        $product = $productQuery->getByShopIdAndGid($this->shopId, $this->product['id']);
        if ($product) {
            $productData = $productTransform->webhookDataToCollectionData($this->product);
            $productCommand->update($product, $productData);
        }
    }
}
