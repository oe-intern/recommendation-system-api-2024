<?php

namespace App\Http\Controllers;

use App\Contracts\Recommendation\IProductInteraction;
use App\Exceptions\ProductNotFoundException;
use App\Jobs\ProcessInteractionEvent;
use App\Lib\Utils;
use App\Services\Shopify\UserContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductInteractionController extends Controller
{
    /**
     * @var UserContext
     */
    protected UserContext $user_context;

    /**
     * @var IProductInteraction
     */
    protected IProductInteraction $product_interaction_service;

    /**
     * ProductInteractionController constructor.
     *
     * @param UserContext $user_context
     * @param IProductInteraction $product_interaction_service
     */
    public function __construct(UserContext $user_context, IProductInteraction $product_interaction_service)
    {
        $this->user_context = $user_context;
        $this->product_interaction_service = $product_interaction_service;
    }

    /**
     * Get list of interactions for a shop.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws ProductNotFoundException
     */
    public function filter(Request $request): JsonResponse
    {
        $shop_domain = $this->user_context->getDomain()->toNative();
        $product_id = $request->query('product_id');
        $product_id = $product_id ? Utils::getIdFromGid($product_id) : null;
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $interactions = $this->product_interaction_service
            ->filter($shop_domain, $product_id, $start_date, $end_date);

        return response()->json($interactions);
    }

    /**
     * Increment the number of interactions for a product.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function interaction(Request $request): JsonResponse
    {
        $shop_domain = $this->user_context->getDomain()->toNative();
        $data = $request->all();

        ProcessInteractionEvent::dispatch($shop_domain, $data);

        return response()->json(['message' => 'Interactions updated successfully']);
    }
}
