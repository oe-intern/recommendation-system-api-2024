<?php

namespace App\Jobs;

use App\Contracts\Commands\Product as ProductCommand;
use App\Contracts\Queries\Shop as ShopQuery;
use App\Storage\Queries\Product as ProductQuery;
use App\Objects\Transform\Product as ProductTransform;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateProduct implements ShouldQueue
{
    use Queueable, Dispatchable;


    /**
     * @var string
     */
    protected string $shop_domain;

    /**
     * @var array
     */
    protected array $product;

    /**
     * Create a new job instance.
     *
     * @param string $shop_domain
     * @param array $product
     */
    public function __construct(string $shop_domain, array $product)
    {
        $this->shop_domain = $shop_domain;
        $this->product = $product;
    }


    /**
     * Execute the job.
     *
     * @param ProductCommand $product_command
     * @param ShopQuery $shop_query
     * @param ProductTransform $product_transform
     * @param ProductQuery $product_query
     * @return void
     */
    public function handle(ProductCommand $product_command, ShopQuery $shop_query, ProductTransform $product_transform, ProductQuery $product_query): void
    {
        $product = $product_query->getByShopDomainAndId($this->shop_domain, $this->product['id']);
        if ($product) {
            $product_data = $product_transform->webhookDataToCollectionData($this->product);
            $product_command->update($product, $product_data);
        }
    }
}
