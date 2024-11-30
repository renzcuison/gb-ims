<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CATEGORIESController;
use App\Http\Controllers\SUPPLIERSController;
use App\Http\Controllers\ITEMSController;
use App\Http\Controllers\STOCKSController;
use App\Http\Controllers\EMPLOYEESController;
use App\Http\Controllers\CUSTOMERSController;
use App\http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::prefix('categories')->group(function () {
        Route::post('/', [CATEGORIESController::class, 'store']);
        Route::put('/{id}', [CATEGORIESController::class, 'update']);
        Route::delete('/{id}', [CATEGORIESController::class, 'destroy']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']); 
        Route::delete('/{id}', [UserController::class, 'destroy']); 
    });
});

Route::prefix('users')->group(function () {
    Route::post('/', [UserController::class, 'register']);  
    Route::post('/login', [UserController::class, 'login']); 
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware([EnsureFrontendRequestsAreStateful::class, 'auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [UserController::class, 'login']);

Route::prefix('users')->group(function () {
Route::post('/', [UserController::class, 'register']); 
Route::get('/', [UserController::class, 'index']);      
Route::get('/{id}', [UserController::class, 'show']);    
Route::put('/{id}', [UserController::class, 'update']);  
Route::delete('/{id}', [UserController::class, 'destroy']); 

});

Route::get('categories', [CATEGORIESController::class, 'index']);
Route::post('categories', [CATEGORIESController::class, 'store']);
Route::get('categories/{id}', [CATEGORIESController::class, 'show']);
Route::put('categories/{id}', [CATEGORIESController::class, 'update']);
Route::delete('categories/{id}', [CATEGORIESController::class, 'destroy']);

Route::get('suppliers', [SUPPLIERSController::class, 'index']);
Route::post('suppliers', [SUPPLIERSController::class, 'store']);
Route::get('suppliers/{id}', [SUPPLIERSController::class, 'show']);
Route::put('suppliers/{id}', [SUPPLIERSController::class, 'update']);
Route::delete('suppliers/{id}', [SUPPLIERSController::class, 'destroy']);

Route::get('items', [ITEMSController::class, 'index']);
Route::post('items', [ITEMSController::class, 'store']);
Route::get('items/{id}', [ITEMSController::class, 'show']);
Route::put('items/{id}', [ITEMSController::class, 'update']);
Route::delete('items/{id}', [ITEMSController::class, 'destroy']);
Route::get('items/check-duplicate', function (Request $request) {
    $itemName = $request->query('name');
    if (!$itemName) {
        return response()->json(['exists' => false], 400);
    }
    $exists = App\Models\Item::where('name', $itemName)->exists();
    return response()->json(['exists' => $exists]);
});

Route::get('stocks', [STOCKSController::class, 'index']);
Route::post('stocks', [STOCKSController::class, 'store']);
Route::get('stocks/{id}', [STOCKSController::class, 'show']);
Route::put('stocks/{id}', [STOCKSController::class, 'update']);
Route::delete('stocks/{id}', [STOCKSController::class, 'destroy']);

Route::get('employees', [EMPLOYEESController::class, 'index']);
Route::post('employees', [EMPLOYEESController::class, 'store']);
Route::get('employees/{id}', [EMPLOYEESController::class, 'show']);
Route::put('employees/{id}', [EMPLOYEESController::class, 'update']);
Route::delete('employees/{id}', [EMPLOYEESController::class, 'destroy']);

Route::get('customers', [CUSTOMERSController::class, 'index']);
Route::post('customers', [CUSTOMERSController::class, 'store']);
Route::get('customers/{id}', [CUSTOMERSController::class, 'show']);
Route::put('customers/{id}', [CUSTOMERSController::class, 'update']);
Route::delete('customers/{id}', [CUSTOMERSController::class, 'destroy']);

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::put('/orders/{id}', [OrderController::class, 'update']);
Route::delete('/orders/{id}', [OrderController::class, 'destroy']);