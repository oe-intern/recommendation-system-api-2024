<?php

declare(strict_types=1);

namespace App\Lib\Handlers;

use App\Jobs\DeleteProduct as DeleteProductJob;

class ProductDelete extends BaseShopHandler
{
    /**
     * Process the incoming webhook data.
     *
     * @param string $shop_domain
     * @param array $body
     * @return void
     */
    protected function processData(string $shop_domain, array $body): void
    {
        DeleteProductJob::dispatch($shop_domain, $body);
    }
}
