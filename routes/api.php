<?php

use App\Http\Middleware\VerifyGatewayToken;
use Illuminate\Support\Facades\Route;

// All routes here are prefixed with /api.
Route::prefix('v1')->group(function () {
    Route::get('/ping', fn () => response()->json([
        'service' => 'todo',
        'status' => 'ok',
    ]));

    // Everything below is only reachable with a gateway-signed token.
    Route::middleware(VerifyGatewayToken::class)->group(function () {
        Route::get('/hello', fn () => response()->json([
            'service' => 'todo',
            'message' => 'Hello from todo',
        ]));
    });
});
