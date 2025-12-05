<?php

declare(strict_types=1);

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rotas da API REST de Pedidos
|
*/

// Listar pedidos (com paginação tradicional)
Route::get('/orders', [OrderController::class, 'index']);

// Listar pedidos (com keyset pagination - diferencial)
Route::get('/orders/cursor', [OrderController::class, 'indexWithCursor']);

// Criar pedido
Route::post('/orders', [OrderController::class, 'store']);

// Visualizar pedido
Route::get('/orders/{id}', [OrderController::class, 'show'])
    ->where('id', '[a-f0-9-]{36}');

// Atualizar status
Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])
    ->where('id', '[a-f0-9-]{36}');

// Excluir pedido (soft delete)
Route::delete('/orders/{id}', [OrderController::class, 'destroy'])
    ->where('id', '[a-f0-9-]{36}');

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
    ]);
});
