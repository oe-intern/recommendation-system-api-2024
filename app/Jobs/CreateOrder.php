<?php

namespace App\Jobs;

use App\Contracts\Commands\IOrderCommand;
use App\Contracts\Commands\IOrderTypeQuantityCommand;
use App\Contracts\Queries\IShopQuery;
use App\Objects\Transform\OrderTransform;
use App\Storage\Queries\ProductQuery;
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
     * @param IOrderCommand $order_command
     * @param IShopQuery $shop_query
     * @param OrderTransform $order_transform
     * @param IOrderTypeQuantityCommand $order_type_quantity_command
     * @param ProductQuery $product_query
     * @return void
     */
    public function handle(
        IOrderCommand $order_command,
        IShopQuery $shop_query,
        OrderTransform $order_transform,
        ProductQuery $product_query,
        IOrderTypeQuantityCommand $order_type_quantity_command
    ): void {
        $shop = $shop_query->getByDomain($this->shop_domain);

        if (!$shop) {
            return;
        }

        $order_data = $order_transform->webhookDataToCollectionData($this->product);
        $products_data = $order_transform->getWebhookLineItems($this->product);

        $order_command->create($order_data, $shop);
        foreach ($products_data as $product_data) {
            $product = $product_query->getByShopDomainAndId($this->shop_domain, $product_data['productId']);
            if ($product) {
                $order_type_quantity_command->increment($shop, $product['productType'], $product_data['quantity']);
            }
        }
    }
}
