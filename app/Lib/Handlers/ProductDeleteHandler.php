<?php

declare(strict_types=1);

namespace App\Lib\Handlers;

use App\Jobs\DeleteProduct as DeleteProductJob;

class ProductDeleteHandler extends BaseShopHandler
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
        DeleteProductJob::dispatch($shop_id, $body);
    }
}
