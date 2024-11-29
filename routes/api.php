<?php

use App\Http\Controllers\ProductInteractionController;
use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['access_control_headers', 'shopify.auth', 'verify.token']], function () {
    Route::prefix('recommendation')->group(function () {
        Route::get('/', [RecommendationController::class, 'getProduct']);
        Route::prefix('settings')->group(function () {
            Route::get('', [RecommendationController::class, 'getAutoRecommendation']);
            Route::put('', [RecommendationController::class, 'setAutoRecommendation'])
                ->middleware('validate.auto.recommendation.request');
        });
        Route::put('/state', [
            RecommendationController::class,
            'setState'
        ])->middleware('validate.recommendation.state.request');
        Route::put('', [
            RecommendationController::class,
            'setManualRecommendation'
        ])->middleware('validate.recommendation.request');
    });
    Route::prefix('interaction')->group(function () {
        Route::get('', [ProductInteractionController::class, 'filter'])
            ->middleware('validate.product.interaction.filter');
    });
});

Route::group(['middleware' => ['access_control_headers', 'identify.shop.domain']], function () {
    Route::prefix("sdk")->group(function () {
        Route::get("/recommendation", [RecommendationController::class, 'getRecommendation']);
        Route::get("/recommendation/settings", [RecommendationController::class, 'getAutoRecommendation']);
        Route::prefix("/interaction")->group(function () {
            Route::post('', [ProductInteractionController::class, 'interaction'])
                ->middleware('validate.product.interaction.request');
        });
    });
});
