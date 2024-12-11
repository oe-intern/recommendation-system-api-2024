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
    protected bool $is_trashed;

    /**
     * @var array
     */
    protected array $products;

    /**
     * Create a new job instance
     *
     * @param string $domain
     * @param bool $is_trashed
     * @param array $products
     */
    public function __construct(string $domain, bool $is_trashed, array $products)
    {
        $this->domain = $domain;
        $this->is_trashed = $is_trashed;
        $this->products = $products;
    }

    /**
     * Execute the job
     *
     * @param IProductQueryShopify $product_query
     * @param IProductCommand $product_command
     * @param IProduct $product_service
     * @param IShopQuery $shop_query
     * @param IShopCommand $shop_command
     * @param ShopifyTransform $product_transform
     * @return void
     *
     * @throws Exception
     */
    public function handle(
        IProductQueryShopify $product_query,
        IProductCommand $product_command,
        IProduct $product_service,
        IShopQuery $shop_query,
        IShopCommand $shop_command,
        ShopifyTransform $product_transform,
    ): void {
        $products_data = $product_transform->shopifyDataListToCollectionDataList($this->products);

        // Create or update the shop and its data
        if ($this->is_trashed) {
            $shop = $shop_query->getByDomain($this->domain);
            $product_service->createOrUpdateMany($shop, $products_data);
        } else {
            $new_shop = $shop_command->create($this->domain);
            $product_command->createMany($new_shop, $products_data);
        }
    }
}
