<?php

namespace App\Services\Product;

use App\Contracts\Recommendation\IRecommendationProcess;
use App\Contracts\Shopify\Graphql\Queries\IOrderQueryShopify;

class RecommendationProcessService implements IRecommendationProcess
{
    /**
     * @var IOrderQueryShopify
     */
    protected IOrderQueryShopify $orderQueryShopify;

    /**
     * @param IOrderQueryShopify $orderQueryShopify
     */
    public function __construct(IOrderQueryShopify $orderQueryShopify)
    {
        $this->orderQueryShopify = $orderQueryShopify;
    }

    /**
     * Process order data.
     *
     * @param string $shop_id
     * @return array
     */
    public function processOrderData(string $shop_id): array
    {
        $order_data = $this->orderQueryShopify->fetchAll();

        $total_orders = count($order_data);
        $type_type_order_count = [];
        $product_product_order_count = [];
        $product_order_count = [];
        $type_order_count = [];

        foreach ($order_data as $order) {
            $line_items = $order['lineItems'] ?? [];
            $product_ids = [];
            $product_types = [];

            foreach ($line_items as $lineItem) {
                $product_id = $lineItem['product']['id'] ?? null;
                $product_type = $lineItem['product']['productType'] ?? null;

                if ($product_id) {
                    $product_ids[] = $product_id;
                    $product_order_count[$product_id] = ($product_order_count[$product_id] ?? 0) + 1;
                }

                if ($product_type) {
                    $product_types[] = $product_type;
                    $type_order_count[$product_type] = ($type_order_count[$product_type] ?? 0) + 1;
                }
            }

            $this->calculateCombinations($product_types, $type_type_order_count);
            $this->calculateCombinations($product_ids, $product_product_order_count);
        }

        $this->calculateRatios($type_type_order_count, $type_order_count);
        $this->calculateRatios($product_product_order_count, $product_order_count);

        return [
            'total' => $total_orders,
            'type_score' => $type_type_order_count,
            'product_score' => $product_product_order_count,
        ];
    }

    /**
     * Calculate the combinations of the items.
     *
     * @param array $items
     * @param $result
     * @return void
     */
    private function calculateCombinations(array $items, &$result): void
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
            $count_item1 = $counts[$item1];
            foreach ($itemCounts as $item2 => &$count) {
                $count = $count / $count_item1;
            }
        }
    }

}
