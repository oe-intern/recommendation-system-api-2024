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
    protected IProductQuery $productQuery;

    /**
     * @var IProductCommand
     */
    protected IProductCommand $productCommand;

    /**
     * ProductService constructor.
     *
     * @param IProductQuery $productQuery
     * @param IProductCommand $productCommand
     */
    public function __construct(IProductQuery $productQuery, IProductCommand $productCommand)
    {
        $this->productQuery = $productQuery;
        $this->productCommand = $productCommand;
    }

    /**
     * Handle update product if exist and create if not
     *
     * @param ShopCollection $shop
     * @param array $productData
     * @return void
     */
    public function createOrUpdateMany(ShopCollection $shop, array $productData): void
    {
        $productGids = $this->getProductGidsFormated($productData);
        $existProductGids = $this->productQuery->getExistingProductsGid($shop->getId(), $productGids);
        $productsToDelete = $this->getDeletedProducts($shop->getId(), $productGids);

        $this->handleUpdatesAndDeletes($existProductGids, $productsToDelete, $productData, $shop);
    }

    /**
     * Get product gids formated
     *
     * @param array $productData
     * @return array
     */
    public function getProductGidsFormated(array $productData): array
    {
        return array_map(function ($product) {
            return Utils::getIdFromGid($product['gid']);
        }, $productData);
    }

    /**
     * Handle update product if existed, create if not and delete if not exist
     *
     * @param array $existingGids
     * @param array $deletedGids
     * @param array $productData
     * @param ShopCollection $shop
     * @return void
     */
    private function handleUpdatesAndDeletes(
        array $existingGids,
        array $deletedGids,
        array $productData,
        ShopCollection $shop,
    ): void {
        $existingProducts = $this->filterProducts($productData, $existingGids, true);
        $newProducts = $this->filterProducts($productData, $existingGids, false);

        $this->processProducts($newProducts, $existingProducts, $deletedGids, $shop);
    }

    /**
     * Filter products
     *
     * @param array $productData
     * @param array $existingGids
     * @param bool $match
     * @return array
     */
    private function filterProducts(array $productData, array $existingGids, bool $match): array
    {
        return array_filter($productData, function ($product) use ($existingGids, $match) {
            return $match === in_array($product['gid'], $existingGids);
        });
    }

    /**
     * Get deleted products not in the list of product gids
     *
     * @param string $shopId
     * @param array $productGids
     * @return array
     */
    private function getDeletedProducts(string $shopId, array $productGids): array
    {
        return $this->productQuery->getProductGidsNotIn($shopId, $productGids);
    }

    /**
     * Process products in database
     *
     * @param array $newProducts
     * @param array $existingProducts
     * @param array $productsToDelete
     * @param ShopCollection $shop
     * @return void
     */
    private function processProducts(
        array $newProducts,
        array $existingProducts,
        array $productsToDelete,
        ShopCollection $shop,
    ): void {
        Log::info('length of new products: ' . count($newProducts));
        $this->deleteProducts($productsToDelete);
        Log::info('length of existing products: ' . count($existingProducts));
        $this->updateProducts($existingProducts);
        Log::info('length of products to delete: ' . count($productsToDelete));
        $this->createProducts($shop, $newProducts);
    }

    /**
     * Delete products
     *
     * @param array $productsToDelete
     * @return void
     */
    private function deleteProducts(array $productsToDelete): void
    {
        $this->productCommand->deleteManyByGid($productsToDelete);
    }

    /**
     * Update products
     *
     * @param array $existingProducts
     * @return void
     */
    private function updateProducts(array $existingProducts): void
    {
        $this->productCommand->updateManyByGid($existingProducts);
    }

    /**
     * Create products
     *
     * @param ShopCollection $shop
     * @param array $newProducts
     * @return void
     */
    private function createProducts(ShopCollection $shop, array $newProducts): void
    {
        $this->productCommand->createMany($shop, $newProducts);
    }
}
