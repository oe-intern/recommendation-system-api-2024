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
     * Convert shopify data to model api data.
     *
     * @param array $data
     * @return array
     */
    public function shopifyDataToModelApiData(array $data): array
    {
        return ([
            'shopify_id' => data_get($data, 'id'),
            'product_type' => data_get($data, 'productType'),
            'name' => data_get($data, 'title'),
            'vendor' => data_get($data, 'vendor'),
            'image' => $this->getImageUrl($data),
        ]);
    }

    /**
     * Convert shopify data list to model api list data.
     *
     * @param array $data
     * @return array
     */
    public function shopifyDataListToModelApiListData(array $data): array
    {
        return array_map(function ($product) {
            return $this->shopifyDataToModelApiData($product);
        }, $data);
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

    /**
     * Get image url of the product.
     *
     * @param array $product
     * @return string|null
     */
    private function getImageUrl(array $product): ?string
    {
        $media = data_get($product, 'featuredMedia');
        $preview = data_get($media, 'preview');
        $image = data_get($preview, 'image');
        return data_get($image, 'url');
    }
}
