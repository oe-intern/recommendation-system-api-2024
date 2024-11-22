<?php

namespace App\Objects\Transform;

use App\Contracts\Objects\Transform\ShopifyTransform as IShopifyTransform;
use App\Lib\Utils;

class Product implements IShopifyTransform
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
            'id' => $this->getShopifyId($data),
            'title' => data_get($data, 'title'),
            'handle' => data_get($data, 'handle'),
            'categoryId' => $this->getShopifyCategoryId($data),
            'vendor' => data_get($data, 'vendor'),
            'variantIds' => $this->getShopifyVariantIds($data),
            'totalInventory' => data_get($data, 'totalInventory'),
            'tags' => $this->getShopifyTags($data),
            'status' => data_get($data, 'status'),
            'productType' => data_get($data, 'productType'),
            'description' => data_get($data, 'description'),
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
            'id' => $this->getWebhookId($data),
            'title' => data_get($data, 'title'),
            'handle' => data_get($data, 'handle'),
            'categoryId' => $this->getWebhookCategoryId($data),
            'vendor' => data_get($data, 'vendor'),
            'variantIds' => $this->getWebhookVariantIds($data),
            'totalInventory' => data_get($data, 'inventory_quantity'),
            'tags' => $this->getWebhookTags($data),
            'status' => data_get($data, 'status'),
            'productType' => data_get($data, 'product_type'),
            'description' => data_get($data, 'body_html'),
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
     * Get category id of the product.
     *
     * @param array $product
     * @return string|null
     */
    private function getShopifyCategoryId(array $product): ?string
    {
        return data_get($product, 'category.id');
    }

    /**
     * Get variant ids of the product.
     *
     * @param array $product
     * @return array
     */
    private function getShopifyVariantIds(array $product): array
    {
        return collect(data_get($product, 'variants', []))->pluck('id')->map(function ($id) {
            return Utils::getIdFromGid($id);
        })->toArray();
    }

    /**
     * Get tags of the product.
     *
     * @param array $product
     * @return array
     */
    private function getShopifyTags(array $product): array
    {
        $tags = data_get($product, 'tags', '');
        return is_array($tags) ? $tags : ($tags !== '' ? explode(', ', $tags) : []);
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
     * Get category id of the product.
     *
     * @param array $product
     * @return string|null
     */
    private function getWebhookCategoryId(array $product): ?string
    {
        return data_get($product, 'category.admin_graphql_api_id');
    }

    /**
     * Get variant ids of the product.
     *
     * @param array $product
     * @return array
     */
    private function getWebhookVariantIds(array $product): array
    {
        return collect(data_get($product, 'variants', []))->pluck('admin_graphql_api_id')->map(function ($id) {
            return Utils::getIdFromGid($id);
        })->toArray();
    }

    /**
     * Get tags of the product.
     *
     * @param array $product
     * @return array
     */
    private function getWebhookTags(array $product): array
    {
        $tags = data_get($product, 'tags', '');
        return is_array($tags) ? $tags : ($tags !== '' ? explode(', ', $tags) : []);
    }
}
