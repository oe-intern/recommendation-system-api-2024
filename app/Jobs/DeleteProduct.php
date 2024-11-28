<?php

namespace App\Jobs;

use App\Contracts\Commands\IProductCommand;
use App\Contracts\Queries\IProductQuery;
use App\Contracts\Queries\IShopQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteProduct implements ShouldQueue
{
    use Queueable, Dispatchable;


    /**
     * @var string
     */
    protected string $shop_domain;

    /**
     * @var array
     */
    protected array $product;

    /**
     * Create a new job instance.
     *
     * @param string $shop_domain
     * @param array $product
     */
    public function __construct(string $shop_domain, array $product)
    {
        $this->shop_domain = $shop_domain;
        $this->product = $product;
    }


    /**
     * Execute the job.
     *
     * @param IProductCommand $product_command
     * @param IShopQuery $shop_query
     * @param IProductQuery $product_query
     * @return void
     */
    public function handle(IProductCommand $product_command, IShopQuery $shop_query, IProductQuery $product_query): void
    {
        $shop = $shop_query->getByDomain($this->shop_domain);

        if (!$shop) {
            return;
        }

        $product = $product_query->getByShopDomainAndId($this->shop_domain, $this->product['id']);
        if ($product) {
            $product_command->delete($product);
        }
    }
}
