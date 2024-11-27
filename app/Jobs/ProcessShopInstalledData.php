<?php

namespace App\Jobs;

use App\Contracts\Commands\IOrderCommand;
use App\Contracts\Commands\IProductCommand;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\Shopify\Graphql\Queries\IOrderQueryShopify;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use App\Objects\Transform\ProductTransform;
use App\Objects\Transform\OrderTransform;
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
     * @param IOrderQueryShopify $order_query
     * @param IShopCommand $shop_command
     * @param IProductCommand $product_command
     * @param IOrderCommand $order_command
     * @param ProductTransform $product_transform
     * @param OrderTransform $order_transform
     * @return void
     */
    public function handle(
        IProductQueryShopify $product_query,
        IOrderQueryShopify $order_query,
        IShopCommand $shop_command,
        IProductCommand $product_command,
        IOrderCommand $order_command,
        ProductTransform $product_transform,
        OrderTransform $order_transform
    ): void {
        // Get the products and orders from the shop
        $products = $product_query->fetchAll();
        $orders = $order_query->fetchAll();

        $products_data = $product_transform->shopifyDataListToCollectionDataList($products);
        $orders_data = $order_transform->shopifyDataListToCollectionDataList($orders);

        // Create the shop and its data
        $new_shop = $shop_command->create($this->domain);
        $product_command->createMany($new_shop, $products_data);
        $order_command->createMany($orders_data, $new_shop);
    }
}
