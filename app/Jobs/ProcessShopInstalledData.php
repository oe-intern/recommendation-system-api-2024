<?php

namespace App\Jobs;

use App\Contracts\Commands\Order as OrderCommand;
use App\Contracts\Commands\Product as ProductCommand;
use App\Contracts\Commands\Shop as ShopCommand;
use App\Contracts\Shopify\Graphql\Queries\Order as OrderQuery;
use App\Contracts\Shopify\Graphql\Queries\Product as ProductQuery;
use App\Objects\Transform\Product as ProductTransform;
use App\Objects\Transform\Order as OrderTransform;
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
     * @param ProductQuery $product_query
     * @param OrderQuery $order_query
     * @param ShopCommand $shop_command
     * @param ProductCommand $product_command
     * @param OrderCommand $order_command
     * @param ProductTransform $product_transform
     * @param OrderTransform $order_transform
     * @return void
     */
    public function handle(
        ProductQuery $product_query,
        OrderQuery $order_query,
        ShopCommand $shop_command,
        ProductCommand $product_command,
        OrderCommand $order_command,
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
        $product_command->createMany($products_data, $new_shop);
        $order_command->createMany($orders_data, $new_shop);
    }
}
