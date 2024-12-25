<?php

namespace App\Services\Recommendation;

use App\Contracts\Recommendation\IRecommendationProcess;
use App\Contracts\Shopify\Graphql\Queries\IOrderQueryShopify;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;

class RecommendationProcessService implements IRecommendationProcess
{
    /**
     * @var IOrderQueryShopify
     */
    protected IOrderQueryShopify $orderQueryShopify;

    /**
     * @var IProductQueryShopify
     */
    protected IProductQueryShopify $productQueryShopify;

    /**
     * @param IOrderQueryShopify $orderQueryShopify
     * @param IProductQueryShopify $productQueryShopify
     */
    public function __construct(
        IOrderQueryShopify $orderQueryShopify,
        IProductQueryShopify $productQueryShopify,
    ) {
        $this->orderQueryShopify = $orderQueryShopify;
        $this->productQueryShopify = $productQueryShopify;
    }

    /**
     * Process order data.
     *
     * @param string $shopId
     * @return array
     */
    public function processOrderData(string $shopId): array
    {
        $orderData = $this->orderQueryShopify->fetchAll();

        $totalOrders = count($orderData);
        $typeTypeOrderCount = [];
        $productProductOrderCount = [];
        $productOrderCount = [];
        $typeOrderCount = [];

        foreach ($orderData as $order) {
            $lineItems = $order['lineItems'] ?? [];
            $productIds = [];
            $productTypes = [];

            foreach ($lineItems as $lineItem) {
                $this->processLineItem($lineItem, $productIds, $productTypes, $productOrderCount, $typeOrderCount);
            }

            $this->updateCombinationCounts($productTypes, $typeTypeOrderCount);
            $this->updateCombinationCounts($productIds, $productProductOrderCount);
        }

        $this->calculateRatios($typeTypeOrderCount, $typeOrderCount);
        $this->calculateRatios($productProductOrderCount, $productOrderCount);

        return [
            'total' => $totalOrders,
            'type_scores' => $typeTypeOrderCount,
            'product_scores' => $productProductOrderCount,
        ];
    }

    /**
     * Process each line item to update product and type counts.
     *
     * @param array $lineItem
     * @param array $productIds
     * @param array $productTypes
     * @param array $productOrderCount
     * @param array $typeOrderCount
     * @return void
     */
    private function processLineItem(
        array $lineItem,
        array &$productIds,
        array &$productTypes,
        array &$productOrderCount,
        array &$typeOrderCount,
    ): void {
        $productId = $lineItem['product']['id'] ?? null;
        $productType = $lineItem['product']['productType'] ?? null;

        if ($productId) {
            $productIds[] = $productId;
            $productOrderCount[$productId] = ($productOrderCount[$productId] ?? 0) + 1;
        }

        if ($productType) {
            $productTypes[] = $productType;
            $typeOrderCount[$productType] = ($typeOrderCount[$productType] ?? 0) + 1;
        }
    }

    /**
     * Calculate the combinations of the items.
     *
     * @param array $items
     * @param $result
     * @return void
     */
    private function updateCombinationCounts(array $items, &$result): void
    {
        $count = count($items);
        for ($i = 0; $i < $count; $i++) {
            $item1 = $items[$i];
            for ($j = $i + 1; $j < $count; $j++) {
                $item2 = $items[$j];
                $result[$item1][$item2] = ($result[$item1][$item2] ?? 0) + 1;
                $result[$item2][$item1] = ($result[$item2][$item1] ?? 0) + 1;
            }
        }
    }

    /**
     * Calculate the ratios of the combinations.
     *
     * @param array $combinations
     * @param array $counts
     * @return void
     */
    private function calculateRatios(array &$combinations, array $counts): void
    {
        foreach ($combinations as $item1 => &$itemCounts) {
            $countItem1 = $counts[$item1];
            foreach ($itemCounts as $item2 => &$count) {
                $count = $count / $countItem1;
            }
        }
    }

    /**
     * Process pre-recommendation data.
     *
     * @param string $shopId
     * @return array
     */
    public function processPreRecommendationData(string $shopId): array {}
}
