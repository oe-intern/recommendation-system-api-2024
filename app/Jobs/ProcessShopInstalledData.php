<?php

namespace App\Jobs;

use App\Contracts\ModelRecommendation\IRecommendationApi;
use App\Contracts\Recommendation\IProductRecommendation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;

class ProcessShopInstalledData implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * @var string
     */
    protected string $shop_id;

    /**
     * @var array
     */
    protected array $products;

    /**
     * @var array
     */
    protected array $data;

    /**
     * Create a new job instance.
     *
     * @param string $shop_id
     * @param array $products
     * @param array $data
     */
    public function __construct(string $shop_id, array $products, array $data)
    {
        $this->shop_id = $shop_id;
        $this->products = $products;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @param IRecommendationApi $recommendation_api_service
     * @param IProductRecommendation $product_recommendation_service
     * @return void
     *
     * @throws Exception
     */
    public function handle(
        IRecommendationApi $recommendation_api_service,
        IProductRecommendation $product_recommendation_service
    ): void {
        $recommendation_data = $recommendation_api_service->preRecommend($this->data);
        $product_recommendation_service->updateManyRecommendation($recommendation_data, $this->shop_id);
    }

}
