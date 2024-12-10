<?php

namespace App\Objects\Transform;

use App\Contracts\Objects\Transform\ShopifyTransform as IShopifyTransform;
use App\Lib\Utils;

class ProductTransform implements IShopifyTransform
{
    /**
     * Convert shopify data list to collection data list.
     *
     * @param array $data
     * @return array
     */
    public function shopifyDataListToCollectionDataList(array $data): array
    {
        return array_map(function ($product) {
            return $this->shopifyDataToCollectionData($product);
        }, $data);
    }

    /**
     * Convert shopify data to collection.
     *
     * @param array $data
     * @return array
     */
    public function shopifyDataToCollectionData(array $data): array
    {
        return ([
            'gid' => $this->getShopifyId($data),
            'status' => data_get($data, 'status'),
            'type' => data_get($data, 'productType'),
            'handle' => data_get($data, 'handle'),
        ]);
    }

    /**
     * Convert webhook data to collection data.
     *
     * @param array $data
     * @return array
     */
    public function webhookDataToCollectionData(array $data): array
    {
        return ([
            'gid' => $this->getWebhookId($data),
            'status' => data_get($data, 'status'),
            'type' => data_get($data, 'product_type'),
            'handle' => data_get($data, 'handle'),
        ]);
    }

    /**
     * Get id of the product.
     *
     * @param array $product
     * @return string
     */
    private function getShopifyId(array $product): string
    {
        return Utils::getIdFromGid(data_get($product, 'id'));
    }

    /**
     * Get id of the product.
     *
     * @param array $product
     * @return string
     */
    private function getWebhookId(array $product): string
    {
        return data_get($product, 'id');
    }
}
