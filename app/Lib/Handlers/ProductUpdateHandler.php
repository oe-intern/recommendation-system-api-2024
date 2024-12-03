<?php

declare(strict_types=1);

namespace App\Lib\Handlers;

use App\Jobs\UpdateProduct as UpdateProductJob;

class ProductUpdateHandler extends BaseShopHandler
{
    /**
     * Process the incoming webhook data.
     *
     * @param string $shop_id
     * @param array $body
     * @return void
     */
    protected function processData(string $shop_id, array $body): void
    {
        UpdateProductJob::dispatch($shop_id, $body);
    }
}
