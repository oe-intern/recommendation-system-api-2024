<?php

namespace App\Storage\Commands;

use App\Collections\Shop as ShopCollection;
use App\Contracts\Commands\Order as OrderCommand;
use App\Objects\Transform\Order as OrderTransform;

class Order implements OrderCommand
{
    /**
     * OrderTransform instance.
     *
     * @var OrderTransform
     */
    protected OrderTransform $order_transform;

    /**
     * Order constructor.
     *
     * @param OrderTransform $order_transform
     */
    public function __construct(OrderTransform $order_transform)
    {
        $this->order_transform = $order_transform;
    }

    /**
     * Create list order of a shop with data from Shopify.
     *
     * @param array $order
     * @param ShopCollection $shop
     * @return void
     */
    public function create(array $order, ShopCollection $shop): void
    {
        $shop->orders()->create($this->order_transform->shopifyDataToCollectionData($order));
    }

    /**
     * Create a list orders of a shop with data from Shopify.
     *
     * @param array $orders
     * @param ShopCollection $shop
     * @return void
     */
    public function createMany(array $orders, ShopCollection $shop): void
    {
        $shop->orders()->createMany($this->order_transform->shopifyDataListToCollectionDataList($orders));
    }

    /**
     * Update list order of a shop with data from Shopify.
     *
     * @param array $order
     * @param ShopCollection $shop
     * @return bool
     */
    public function update(array $order, ShopCollection $shop): bool
    {
        $order_data = $this->order_transform->shopifyDataToCollectionData($order);
        $existing_order = $shop->orders()->find($order_data['id']);

        if ($existing_order) {
            return $existing_order->update($order_data);
        }

        return false;
    }
}
