<?php

namespace App\Services\Recommendation;

use App\Collections\ShopCollection;
use App\Contracts\Commands\IProductCommand;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Recommendation\IProduct;
use App\Lib\Utils;
use Illuminate\Support\Facades\Log;

class ProductService implements IProduct
{
    /**
     * @var IProductQuery
     */
    protected IProductQuery $product_query;

    /**
     * @var IProductCommand
     */
    protected IProductCommand $product_command;

    /**
     * ProductService constructor.
     *
     * @param IProductQuery $product_query
     * @param IProductCommand $product_command
     */
    public function __construct(IProductQuery $product_query, IProductCommand $product_command)
    {
        $this->product_query = $product_query;
        $this->product_command = $product_command;
    }

    /**
     * Handle update product if exist and create if not
     *
     * @param ShopCollection $shop
     * @param array $product_data
     * @return void
     */
    public function createOrUpdateMany(ShopCollection $shop, array $product_data): void
    {
        $product_gids = $this->getProductGidsFormated($product_data);
        Log::info('length of product data: ' . count($product_data));
        $exist_product_gids = $this->product_query->getExistingProductsGid($shop->getId(), $product_gids);
        Log::info('length of exist product gids: ' . count($exist_product_gids));
        $this->handleUpdatesAndDeletes($exist_product_gids, $product_gids, $product_data, $shop);
    }

    /**
     * Get product gids formated
     *
     * @param array $product_data
     * @return array
     */
    public function getProductGidsFormated(array $product_data): array
    {
        return array_map(function ($product) {
            return Utils::getIdFromGid($product['gid']);
        }, $product_data);
    }

    /**
     * Handle update product if exist, create if not and delete if not exist
     *
     * @param array $existing_gids
     * @param array $product_gids
     * @param array $product_data
     * @param ShopCollection $shop
     * @return void
     */
    private function handleUpdatesAndDeletes(
        array $existing_gids,
        array $product_gids,
        array $product_data,
        ShopCollection $shop,
    ): void {
        $existing_products = $this->filterProducts($product_data, $existing_gids, true);
        $new_products = $this->filterProducts($product_data, $existing_gids, false);
        $products_to_delete = array_diff($existing_gids, $product_gids);

        $this->processProducts($new_products, $existing_products, $products_to_delete, $shop);
    }

    /**
     * Filter products
     *
     * @param array $product_data
     * @param array $existing_gids
     * @param bool $match
     * @return array
     */
    private function filterProducts(array $product_data, array $existing_gids, bool $match): array
    {
        return array_filter($product_data, function ($product) use ($existing_gids, $match) {
            return $match === in_array($product['gid'], $existing_gids);
        });
    }

    /**
     * Process products in database
     *
     * @param array $new_products
     * @param array $existing_products
     * @param array $products_to_delete
     * @param ShopCollection $shop
     * @return void
     */
    private function processProducts(
        array $new_products,
        array $existing_products,
        array $products_to_delete,
        ShopCollection $shop,
    ): void {
        Log::info('length of new products: ' . count($new_products));
        $this->deleteProducts($products_to_delete);
        Log::info('length of existing products: ' . count($existing_products));
        $this->updateProducts($existing_products);
        Log::info('length of products to delete: ' . count($products_to_delete));
        $this->createProducts($shop, $new_products);
    }

    /**
     * Delete products
     *
     * @param array $products_to_delete
     * @return void
     */
    private function deleteProducts(array $products_to_delete): void
    {
        $this->product_command->deleteManyByGid($products_to_delete);
    }

    /**
     * Update products
     *
     * @param array $existing_products
     * @return void
     */
    private function updateProducts(array $existing_products): void
    {
        $this->product_command->updateManyByGid($existing_products);
    }

    /**
     * Create products
     *
     * @param ShopCollection $shop
     * @param array $new_products
     * @return void
     */
    private function createProducts(ShopCollection $shop, array $new_products): void
    {
        $this->product_command->createMany($shop, $new_products);
    }
}
