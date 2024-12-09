<?php

use App\Http\Controllers\ProductEventController;
use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['access_control_headers', 'shopify.auth', 'verify.token']], function () {
    Route::prefix('products')->group(function () {
        Route::get('/{product_id}', [RecommendationController::class, 'getProduct']);
        Route::get('/{product_id}/recommendation-type',
            [RecommendationController::class, 'getRecommendationTypes']);
        Route::put('/{product_id}/recommendation-type', [RecommendationController::class, 'setRecommendationType'])
            ->middleware('validate.product.recommendation_type.request');
        Route::get('/{product_id}/manual-recommendation',
            [RecommendationController::class, 'getManualRecommendation']);
        Route::put('/{product_id}/manual-recommendation', [
            RecommendationController::class,
            'setManualRecommendation'
        ])->middleware('validate.product.recommendation.request');
    });
    Route::prefix('events')->group(function () {
        Route::group(['middleware' => 'validate.event.analytic'], function () {
            Route::get('/click/analytic', [ProductEventController::class, 'getClickAnalytic']);
            Route::get('/add-to-cart/analytic', [ProductEventController::class, 'getAddToCartAnalytic']);
        });
        Route::get('/performance', [ProductEventController::class, 'getProductPerformance'])
            ->middleware('validate.event.performance.request');
    });
    Route::prefix('shop')->group(function () {
        Route::prefix('settings')->group(function () {
            Route::get('', [RecommendationController::class, 'getShopSetting']);
            Route::put('', [RecommendationController::class, 'setShopSetting'])
                ->middleware('validate.shop.auto_recommendation.request');
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
        });
        Route::prefix('/events')->group(function () {
            Route::post('/click', [
                ProductEventController::class,
                'click'
            ])->middleware('validate.event.click.request');
            Route::post('/add-to-cart', [
                ProductEventController::class,
                'addToCart'
            ])->middleware('validate.event.add_to_cart.request');
        });
    });
});
