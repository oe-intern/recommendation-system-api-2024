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
     * @var array
     */
    protected array $map_gid_id;

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
     * @param array $map_gid_id
     * @param array $products
     * @param array $data
     */
    public function __construct(array $map_gid_id, array $products, array $data)
    {
        $this->map_gid_id = $map_gid_id;
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
        $recommendation_data = $recommendation_api_service->recommend($this->data);
        $product_recommendation_service->updateManyRecommendation($recommendation_data, $this->map_gid_id);
    }

}
