<?php

use App\Http\Controllers\ProductEventController;
use App\Http\Controllers\ProductRecommendationController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ShopSettingController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['access_control_headers', 'shopify.auth', 'verify.token']], function () {
    Route::prefix('products')->group(function () {
        Route::get('/{product_id}/recommendation-type',
            [ProductRecommendationController::class, 'getRecommendationType']);
        Route::put('/{product_id}/recommendation-type',
            [ProductRecommendationController::class, 'setRecommendationType']);
        Route::get('/{product_id}/manual-recommendation',
            [ProductRecommendationController::class, 'getManualRecommendation']);
        Route::put('/{product_id}/manual-recommendation', [
            ProductRecommendationController::class,
            'setManualRecommendation',
        ]);
    });
    Route::prefix('events')->group(function () {
        Route::get('/click/analytic', [ProductEventController::class, 'getClickAnalytic']);
        Route::get('/add-to-cart/analytic', [ProductEventController::class, 'getAddToCartAnalytic']);
        Route::get('/performance', [ProductEventController::class, 'getProductPerformance']);
    });
    Route::prefix('shop')->group(function () {
        Route::prefix('settings')->group(function () {
            Route::get('', [ShopSettingController::class, 'getShopSetting']);
            Route::put('', [ShopSettingController::class, 'setShopSetting']);
            Route::put('/auto_recommendation', [ShopSettingController::class, 'activateRecommendation']);
        });
        Route::prefix('recommendations')->group(function () {
            Route::get('', [RecommendationController::class, 'getShopRecommendations']);
            Route::put('', [RecommendationController::class, 'setShopRecommendations']);
            Route::get('/status', [RecommendationController::class, 'getProcessRecommendation']);
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
            Route::post('/click', [ProductEventController::class, 'click']);
            Route::post('/add-to-cart', [ProductEventController::class, 'addToCart']);
        });
    });
});
