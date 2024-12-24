<?php

namespace App\Jobs;

use App\Contracts\Commands\IProductCommand;
use App\Contracts\Commands\IShopCommand;
use App\Contracts\Objects\Transform\ShopifyTransform;
use App\Contracts\Queries\IShopQuery;
use App\Contracts\Recommendation\IProduct;
use App\Contracts\Shopify\Graphql\Queries\IProductQueryShopify;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;

class PreProcessShopInstalledData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected string $domain;

    /**
     * @var bool
     */
    protected bool $isTrashed;

    /**
     * @var array
     */
    protected array $products;

    /**
     * Create a new job instance
     *
     * @param string $domain
     * @param bool $isTrashed
     * @param array $products
     */
    public function __construct(string $domain, bool $isTrashed, array $products)
    {
        $this->domain = $domain;
        $this->isTrashed = $isTrashed;
        $this->products = $products;
    }

    /**
     * Execute the job
     *
     * @param IProductQueryShopify $productQuery
     * @param IProductCommand $productCommand
     * @param IProduct $productService
     * @param IShopQuery $shopQuery
     * @param IShopCommand $shopCommand
     * @param ShopifyTransform $productTransform
     * @return void
     *
     * @throws Exception
     */
    public function handle(
        IProductQueryShopify $productQuery,
        IProductCommand $productCommand,
        IProduct $productService,
        IShopQuery $shopQuery,
        IShopCommand $shopCommand,
        ShopifyTransform $productTransform,
    ): void {
        $productsData = $productTransform->shopifyDataListToCollectionDataList($this->products);

        // Create or update the shop and its data
        if ($this->isTrashed) {
            $shop = $shopQuery->getByDomain($this->domain);
            $productService->createOrUpdateMany($shop, $productsData);
        } else {
            $newShop = $shopCommand->create($this->domain);
            $productCommand->createMany($newShop, $productsData);
        }
    }
}
