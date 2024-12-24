<?php

namespace App\Jobs;

use App\Contracts\Commands\IProductCommand;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use App\Objects\Transform\ProductTransform;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateProduct implements ShouldQueue
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
     * @param IProductQuery $productQuery
     * @param ProductTransform $productTransform
     * @return void
     */
    public function handle(
        IProductCommand $productCommand,
        IShopQuery $shopQuery,
        IProductQuery $productQuery,
        ProductTransform $productTransform
    ): void {
        $shop = $shopQuery->getById($this->shopId);

        if (!$shop) {
            return;
        }

        $product = $productQuery->getByShopIdAndGid($this->shopId, $this->product['id']);
        if (!$product) {
            $productData = $productTransform->webhookDataToCollectionData($this->product);
            $newProduct = $productCommand->create($shop, $productData);

            UpdateRecommendationProduct::dispatch($newProduct->getId(), $shop->getDomain());
        }
    }
}
