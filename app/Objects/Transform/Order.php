<?php

namespace App\Objects\Transform;

use App\Contracts\Objects\Transform\ShopifyTransform as IShopifyTransform;

class Order implements IShopifyTransform
{
    /**
     * Convert shopify list data to collection list data.
     *
     * @param array $data
     * @return array
     */
    public function shopifyDataListToCollectionDataList(array $data): array
    {
        return array_map(function ($data) {
            return $this->shopifyDataToCollectionData($data);
        }, $data);
    }

    /**
     * Convert shopify data to collection data.
     *
     * @param array $data
     * @return array
     */
    public function shopifyDataToCollectionData(array $data): array
    {
        return [
            '_id' => data_get($data, 'id'),
            'email' => data_get($data, 'email'),
            'amount' => $this->getAmount($data),
            'currencyCodeMoney' => $this->getCurrentCodeMoney($data),
            'createdAt' => data_get($data, 'createdAt'),
            'items' => $this->getLineItems($data),
        ];
    }

    /**
     * Get amount of order.
     *
     * @param array $data
     * @return float
     */
    private function getAmount(array $data): float
    {
        return data_get($data, 'totalPriceSet.presentmentMoney.amount');
    }

    /**
     * Get current code money.
     *
     * @param array $data
     * @return string
     */
    private function getCurrentCodeMoney(array $data): string
    {
        return data_get($data, 'totalPriceSet.presentmentMoney.currencyCode');
    }

    /**
     * Get list item in order.
     *
     * @param array $data
     * @return array
     */
    private function getLineItems(array $data): array
    {
        return array_map(function ($lineItem) {
            return [
                'productId' => data_get($lineItem, 'product.id'),
                'name' => data_get($lineItem, 'name'),
                'quantity' => data_get($lineItem, 'quantity'),
            ];
        }, data_get($data, 'lineItems', collect([]))->toArray());
    }
}
