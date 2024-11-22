<?php

namespace App\Jobs;

use App\Contracts\Commands\Order as OrderCommand;
use App\Contracts\Commands\OrderTypeQuantity as OrderTypeQuantityCommand;
use App\Contracts\Queries\Shop as ShopQuery;
use App\Objects\Transform\Order as OrderTransform;
use App\Storage\Queries\Product as ProductQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateOrder implements ShouldQueue
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
     * @param OrderCommand $order_command
     * @param ShopQuery $shop_query
     * @param OrderTransform $order_transform
     * @param OrderTypeQuantityCommand $order_type_quantity_command
     * @param ProductQuery $product_query
     * @return void
     */
    public function handle(
        OrderCommand $order_command,
        ShopQuery $shop_query,
        OrderTransform $order_transform,
        ProductQuery $product_query,
        OrderTypeQuantityCommand $order_type_quantity_command
    ): void {
        $shop = $shop_query->getByDomain($this->shop_domain);

        if (!$shop) {
            return;
        }

        $order_data = $order_transform->webhookDataToCollectionData($this->product);
        $products_data = $order_transform->getWebhookLineItems($this->product);

        $order_command->create($order_data, $shop);
        foreach ($products_data as $product_data) {
            $product = $product_query->getByIdAndShopCollection($product_data['productId'], $shop);
            if ($product) {
                $order_type_quantity_command->increment($shop, $product['productType'], $product_data['quantity']);
            }
        }
    }
}
