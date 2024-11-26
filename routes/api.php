<?php

use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['access_control_headers', 'shopify.auth', 'verify.token']], function () {
    Route::prefix('recommendation')->group(function () {
        Route::get('/', [RecommendationController::class, 'getProduct']);
        Route::put('/state', [
            RecommendationController::class,
            'setState'
        ])->middleware('validate.recommendation.state.request');
        Route::put('', [
            RecommendationController::class,
            'setManualRecommendation'
        ])->middleware('validate.recommendation.request');
    });
});

Route::group(['middleware' => ['access_control_headers', 'identify.shop.domain']], function () {
    Route::prefix("sdk")->group(function () {
        Route::get("recommendation", [RecommendationController::class, 'getRecommendation']);
    });
});
