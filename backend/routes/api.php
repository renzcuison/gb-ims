<?php

use Illuminate\Http\Request;
use App\Http\Controllers\CATEGORIESController;
use App\Http\Controllers\SUPPLIERSController;
use App\Http\Controllers\STOCKSController;
use App\Http\Controllers\EMPLOYEESController;
use App\Http\Controllers\CUSTOMERSController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StockTransactionsController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\Auth\VerificationController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
Route::prefix('categories')->group(function () {
    Route::post('/', [CATEGORIESController::class, 'store']);
    Route::put('/{id}', [CATEGORIESController::class, 'update']);
    Route::delete('/{id}', [CATEGORIESController::class, 'destroy']);
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

Route::prefix('suppliers')->group(function () {
    Route::post('/', [SUPPLIERSController::class, 'store']);
    Route::put('/{id}', [SUPPLIERSController::class, 'update']);
    Route::delete('/{id}', [SUPPLIERSController::class, 'destroy']);
}); 

Route::prefix('stocks')->group(function () {
    Route::post('/', [STOCKSController::class, 'store']);
    Route::put('/{id}', [STOCKSController::class, 'update']);
    Route::delete('/{id}', [STOCKSController::class, 'destroy']);
    Route::get('/', [STOCKSController::class, 'index']);
    Route::get('/{id}', [STOCKSController::class, 'show']);

    Route::post('/{stockId}/skus', [STOCKSController::class, 'addSku']);

    Route::delete('/{stockId}/skus/{skuId}', [STOCKSController::class, 'removeSku']);
});

Route::prefix('transactions')->group(function () {
    Route::post('/', [StockTransactionsController::class, 'createTransaction']);
    Route::get('/{id}/receipt', [StockTransactionsController::class, 'getReceipt']);
});

// });

Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['auth:sanctum'])
    ->name('verification.verify');


Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified successfully.']);
})->middleware(['auth:sanctum'])
    ->name('verification.verify');

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

Route::post('/login', [UserController::class, 'login'])->name('login');

Route::prefix('users')->group(function () {
    Route::post('/', [UserController::class, 'register']);
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

Route::get('categories', [CATEGORIESController::class, 'index']);
Route::get('categories/{id}', [CATEGORIESController::class, 'show']);

Route::get('suppliers', [SUPPLIERSController::class, 'index']);
Route::get('suppliers/{id}', [SUPPLIERSController::class, 'show']);

Route::get('stocks', [STOCKSController::class, 'index']);
Route::get('stocks/{id}', [STOCKSController::class, 'show']);

Route::get('stocks/check-duplicate', function (Request $request) {
    $itemName = $request->query('item_name');
    $unitOfMeasure = $request->query('unit_of_measure');

    if (!$itemName || !$unitOfMeasure) {
        return response()->json(['exists' => false], 400);
    }

    $exists = App\Models\Stock::where('item_name', $itemName)
        ->where('unit_of_measure', $unitOfMeasure)
        ->exists();

    return response()->json(['exists' => $exists]);
});

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

Route::post('/customer-orders', [CustomerOrderController::class, 'store']);
Route::get('/customer-orders', [CustomerOrderController::class, 'index']);
Route::delete('/customer-orders/{id}', [CustomerOrderController::class, 'destroy']);


Route::get('/transactions/stock/{id}', [StockTransactionsController::class, 'fetchTransactionsByStock']);

