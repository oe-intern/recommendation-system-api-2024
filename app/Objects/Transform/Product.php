<?php

namespace App\Objects\Transform;

use App\Contracts\Objects\Transform\ShopifyTransform as IShopifyTransform;

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
            'id' => data_get($data, 'id'),
            'title' => data_get($data, 'title'),
            'handle' => data_get($data, 'handle'),
            'categoryId' => $this->getCategoryId($data),
            'vendor' => data_get($data, 'vendor'),
            'variantIds' => $this->getVariantIds($data),
            'totalInventory' => data_get($data, 'totalInventory'),
            'tags' => $this->getTags($data),
            'status' => data_get($data, 'status'),
            'productType' => data_get($data, 'productType'),
            'optionIds' => $this->getOptionIds($data),
            'description' => data_get($data, 'description'),
        ]);
    }

    /**
     * Get category id of the product.
     *
     * @param array $product
     * @return string|null
     */
    private function getCategoryId(array $product): ?string
    {
        return data_get($product, 'category.id');
    }

    /**
     * Get variant ids of the product.
     *
     * @param array $product
     * @return array
     */
    private function getVariantIds(array $product): array
    {
        return collect(data_get($product, 'variants', []))->pluck('id')->toArray();
    }

    /**
     * Get tags of the product.
     *
     * @param array $product
     * @return array
     */
    private function getTags(array $product): array
    {
        return data_get($product, 'tags', []);
    }

    /**
     * Get option ids of the product.
     *
     * @param array $product
     * @return array
     */
    private function getOptionIds(array $product): array
    {
        return collect(data_get($product, 'options', []))->pluck('optionValues.*.id')->flatten()->toArray();
    }
}
