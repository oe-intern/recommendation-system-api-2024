<?php

use App\Http\Controllers\ProductEventController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ShopSettingController;
use App\Http\Controllers\ProductRecommendationController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['access_control_headers', 'shopify.auth', 'verify.token']], function () {
    Route::prefix('products')->group(function () {
        Route::get('/{product_id}/recommendation-type',
            [ProductRecommendationController::class, 'getRecommendationTypes']);
        Route::put('/{product_id}/recommendation-type', [ProductRecommendationController::class, 'setRecommendationType'])
            ->middleware('validate.product.recommendation_type.request');
        Route::get('/{product_id}/manual-recommendation',
            [ProductRecommendationController::class, 'getManualRecommendation']);
        Route::put('/{product_id}/manual-recommendation', [
            ProductRecommendationController::class,
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
            Route::get('', [ShopSettingController::class, 'getShopSetting']);
            Route::put('', [ShopSettingController::class, 'setShopSetting'])
                ->middleware('validate.shop.settings.request');
            Route::put('/auto_recommendation', [ShopSettingController::class, 'activateRecommendation'])
                ->middleware('validate.shop.auto_recommendation.request');
        });
        Route::prefix('recommendations')->group(function () {
            Route::get('', [RecommendationController::class, 'getShopRecommendations']);
            Route::put('', [RecommendationController::class, 'setShopRecommendations'])
                ->middleware('validate.shop.notification_settings.request');
            Route::get('/process', [RecommendationController::class, 'processRecommendation']);
            Route::post('/refresh', [RecommendationController::class, 'refreshRecommendation']);
            Route::post('/cancel', [RecommendationController::class, 'cancelRecommendation']);
        });
    });
});

Route::group(['middleware' => ['access_control_headers', 'identify.shop.domain']], function () {
    Route::prefix("sdk")->group(function () {
        Route::prefix('/shop')->group(function () {
            Route::prefix('settings')->group(function () {
                Route::get('', [ShopSettingController::class, 'getShopSetting']);
            });
        });
        Route::prefix('/products')->group(function () {
            Route::get('/{product_id}/recommendations',
                [ProductRecommendationController::class, 'getRecommendation']);
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
