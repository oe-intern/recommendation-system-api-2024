<?php

namespace App\Jobs;

use App\Contracts\Commands\IProductCommand;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\Objects\Transform\ProductTransform;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessShopInstalledData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected string $domain;

    /**
     * @param string $domain
     */
    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Execute the job
     *
     * @param IProductQueryShopify $product_query
     * @param IShopCommand $shop_command
     * @param IProductCommand $product_command
     * @param ProductTransform $product_transform
     * @return void
     */
    public function handle(
        IProductQueryShopify $product_query,
        IShopCommand $shop_command,
        IProductCommand $product_command,
        ProductTransform $product_transform,
    ): void {
        // Get the products from the shop
        $products = $product_query->fetchAll();

        $products_data = $product_transform->shopifyDataListToCollectionDataList($products);

        // Create the shop and its data
        $new_shop = $shop_command->create($this->domain);
        $product_command->createMany($new_shop, $products_data);
    }
}
