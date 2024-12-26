<?php

namespace App\Jobs;

use App\Contracts\Recommendation\IProductEvent;
use App\DTO\Request\ClickEventRequestDTO;
use App\Exceptions\ProductNotFoundException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessClickEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected string $shopId;

    /**
     * @var ClickEventRequestDTO
     */
    protected ClickEventRequestDTO $clickEventRequestDTO;

    /**
     * @param string $shopId
     * @param ClickEventRequestDTO $clickEventRequestDTO
     */
    public function __construct(string $shopId, ClickEventRequestDTO $clickEventRequestDTO)
    {
        $this->shopId = $shopId;
        $this->clickEventRequestDTO = $clickEventRequestDTO;
    }

    /**
     * Execute the job
     *
     * @param IProductEvent $productEventService
     * @return void
     *
     * @throws ProductNotFoundException
     */
    public function handle(IProductEvent $productEventService): void
    {
        $productEventService->click(
            $this->shopId,
            $this->clickEventRequestDTO,
        );
    }
}
