<?php

namespace App\Objects\Transform;

use App\Contracts\Objects\Transform\ShopifyTransform as IShopifyTransform;
use App\Lib\Utils;

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
            '_id' => $this->getShopifyId($data),
            'email' => data_get($data, 'email'),
            'amount' => $this->getShopifyAmount($data),
            'currencyCodeMoney' => $this->getShopifyCurrentCodeMoney($data),
            'createdAt' => data_get($data, 'createdAt'),
            'items' => $this->getShopifyLineItems($data),
        ];
    }

    private function getShopifyId(array $data): string
    {
        return Utils::getIdFromGid(data_get($data, 'id'));
    }

    /**
     * Get amount of order.
     *
     * @param array $data
     * @return float
     */
    private function getShopifyAmount(array $data): float
    {
        return (float)data_get($data, 'totalPriceSet.presentmentMoney.amount');
    }

    /**
     * Get current code money.
     *
     * @param array $data
     * @return string
     */
    private function getShopifyCurrentCodeMoney(array $data): string
    {
        return data_get($data, 'totalPriceSet.presentmentMoney.currencyCode');
    }

    /**
     * Get list item in order.
     *
     * @param array $data
     * @return array
     */
    private function getShopifyLineItems(array $data): array
    {
        return array_map(function ($lineItem) {
            return [
                'productId' => Utils::getIdFromGid(data_get($lineItem, 'id')),
                'name' => data_get($lineItem, 'name'),
                'quantity' => data_get($lineItem, 'quantity'),
            ];
        }, data_get($data, 'lineItems', collect([]))->toArray());
    }

    public function webhookDataToCollectionData(array $data): array
    {
        return [
            '_id' => data_get($data, 'id'),
            'email' => data_get($data, 'email'),
            'amount' => $this->getWebhookAmount($data),
            'currencyCodeMoney' => $this->getWebhookCurrentCodeMoney($data),
            'createdAt' => data_get($data, 'created_at'),
            'items' => $this->getWebhookLineItems($data),
        ];
    }

    /**
     * Get amount of order.
     *
     * @param array $data
     * @return float|null
     */
    private function getWebhookAmount(array $data): ?float
    {
        return (float)data_get($data, 'current_total_price_set.presentment_money.amount');
    }

    /**
     * Get current code money.
     *
     * @param array $data
     * @return string
     */
    private function getWebhookCurrentCodeMoney(array $data): string
    {
        return data_get($data, 'current_total_price_set.presentment_money.currency_code');
    }

    /**
     * Get list item in order.
     *
     * @param array $data
     * @return array
     */
    public function getWebhookLineItems(array $data): array
    {
        $line_items = collect(data_get($data, 'line_items', []));

        return $line_items->map(function ($line_item) {
            return [
                'productId' => data_get($line_item, 'product_id'),
                'name' => data_get($line_item, 'name'),
                'quantity' => (int)data_get($line_item, 'quantity'),
            ];
        })->toArray();
    }
}
