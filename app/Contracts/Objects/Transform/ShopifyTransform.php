<?php

namespace App\Contracts\Objects\Transform;

interface ShopifyTransform
{
    /**
     * Convert data from shopify to collection data.
     *
     * @param array $data
     * @return array
     */
    public function shopifyDataToCollectionData(array $data): array;

    /**
     * Convert list of data from shopify to list of collection data.
     *
     * @param array $data
     * @return array
     */
    public function shopifyDataListToCollectionDataList(array $data): array;

    /**
     * Convert webhook data to collection data.
     *
     * @param array $data
     * @return array
     */
    public function webhookDataToCollectionData(array $data): array;
}
