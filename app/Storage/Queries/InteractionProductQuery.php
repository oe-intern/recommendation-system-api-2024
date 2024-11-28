<?php

namespace App\Storage\Queries;

use App\Collections\ProductCollection;
use App\Contracts\Queries\IInteractionQuery;
use App\Contracts\Queries\IShopQuery;
use Carbon\Carbon;

class InteractionProductQuery implements IInteractionQuery
{
    /**
     * @var IShopQuery
     */
    protected IShopQuery $shop_query;

    /**
     * InteractionProductQuery constructor.
     *
     * @param IShopQuery $shop_query
     */
    public function __construct(IShopQuery $shop_query)
    {
        $this->shop_query = $shop_query;
    }

    /**
     * Filter list of interactions for a shop or product.
     *
     * @param string $shop_domain
     * @param ProductCollection|null $product
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function filter(
        string $shop_domain,
        ?ProductCollection $product,
        string $start_date,
        string $end_date
    ): array {
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);

        if ($product) {
            return $this->filterProduct($product, $start_date, $end_date);
        }

        return $this->filterShop($shop_domain, $start_date, $end_date);

    }

    /**
     * Filter list of interactions for a product.
     *
     * @param ProductCollection $product
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return array
     */
    private function filterProduct(ProductCollection $product, Carbon $start_date, Carbon $end_date): array
    {
        return $product->interactions()
            ->get()
            ->whereBetween('date', [$start_date, $end_date])
            ->all();
    }

    /**
     * Filter list of interactions for a shop.
     *
     * @param string $shop_domain
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return array
     */
    private function filterShop(string $shop_domain, Carbon $start_date, Carbon $end_date): array
    {
        $shop = $this->shop_query->getByDomain($shop_domain);

        return $shop->interactions()
            ->get()
            ->whereBetween('date', [$start_date, $end_date])
            ->toArray();
    }
}
