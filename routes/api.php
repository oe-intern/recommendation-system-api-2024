<?php

use App\Http\Controllers\ProductInteractionController;
use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['access_control_headers', 'shopify.auth', 'verify.token']], function () {
    Route::prefix('products')->group(function () {
        Route::get('/{product_id}', [RecommendationController::class, 'getProduct']);
        Route::get('/{product_id}/recommendation-type',
            [RecommendationController::class, 'getRecommendationTypes']);
        Route::group(['middleware' => 'validate.product.statistics'], function () {
            Route::get('/click/statistics',
                [ProductInteractionController::class, 'getClickStatistics']);
            Route::get('/add-to-cart/statistics',
                [ProductInteractionController::class, 'getAddToCartStatistics']);
        });
        Route::put('/{product_id}/recommendation-type', [RecommendationController::class, 'setRecommendationType'])
            ->middleware('validate.recommendation.type.request');
        Route::get('/{product_id}/manual-recommendation',
            [RecommendationController::class, 'getManualRecommendation']);
        Route::put('/{product_id}/manual-recommendation', [
            RecommendationController::class,
            'setManualRecommendation'
        ])->middleware('validate.recommendation.request');
    });
    Route::prefix('shop')->group(function () {
        Route::prefix('settings')->group(function () {
            Route::get('', [RecommendationController::class, 'getShopSetting']);
            Route::put('', [RecommendationController::class, 'setShopSetting'])
                ->middleware('validate.auto.recommendation.request');
        });
    });
});

Route::group(['middleware' => ['access_control_headers', 'identify.shop.domain']], function () {
    Route::prefix("sdk")->group(function () {
        Route::prefix('/shop')->group(function () {
            Route::prefix('settings')->group(function () {
                Route::get('', [RecommendationController::class, 'getShopSetting']);
            });
        });
        Route::prefix('/products')->group(function () {
            Route::get('/{product_id}/recommendations',
                [RecommendationController::class, 'getRecommendation']);
            Route::post('/{product_id}/click', [ProductInteractionController::class, 'click']);
            Route::post('/{product_id}/add-to-cart', [
                ProductInteractionController::class,
                'addToCart'
            ])->middleware('validate.product.addToCart.request');
        });
    });
});
