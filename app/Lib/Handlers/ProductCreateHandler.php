<?php

declare(strict_types=1);

namespace App\Lib\Handlers;

use App\Jobs\CreateProduct as CreateProductJob;

class ProductCreateHandler extends BaseShopHandler
{
    /**
     * Process the incoming webhook data.
     *
     * @param string $shopId
     * @param array $body
     * @return void
     */
    protected function processData(string $shopId, array $body): void
    {
        CreateProductJob::dispatch($shopId, $body)
            ->onQueue(config('queue.queues.product'));
    }
}
