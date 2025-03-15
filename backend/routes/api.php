<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\CATEGORIESController;
use App\Http\Controllers\SUPPLIERSController;
use App\Http\Controllers\STOCKSController;
use App\Http\Controllers\EMPLOYEESController;
use App\Http\Controllers\CUSTOMERSController;
use App\Http\Controllers\STOCKLOGController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// ✅ Check if the user is authenticated
Route::middleware(['auth:sanctum'])->get('/auth-check', function () {
    return response()->json(['user' => auth()->user()]);
});

// ✅ Authentication Routes
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register']);

// ✅ Get Authenticated User Details
Route::middleware([EnsureFrontendRequestsAreStateful::class, 'auth:sanctum'])->get('/user', function (Request $request) {
    return response()->json([
        'id' => $request->user()->id,
        'name' => $request->user()->name,
        'role' => $request->user()->role,
    ]);
});

// ✅ User Management (Admin Only)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

// ✅ Categories Routes (Admin Only)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('categories')->group(function () {
    Route::post('/', [CATEGORIESController::class, 'store']);
    Route::put('/{id}', [CATEGORIESController::class, 'update']);
    Route::delete('/{id}', [CATEGORIESController::class, 'destroy']);
});
Route::get('/categories', [CATEGORIESController::class, 'index']);
Route::get('/categories/{id}', [CATEGORIESController::class, 'show']);

// ✅ Suppliers Routes (Admin Only)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('suppliers')->group(function () {
    Route::post('/', [SUPPLIERSController::class, 'store']);
    Route::put('/{id}', [SUPPLIERSController::class, 'update']);
    Route::delete('/{id}', [SUPPLIERSController::class, 'destroy']);
});
Route::get('/suppliers', [SUPPLIERSController::class, 'index']);
Route::get('/suppliers/{id}', [SUPPLIERSController::class, 'show']);

// ✅ Stock Management (Admin Only)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('stocks')->group(function () {
    Route::post('/', [STOCKSController::class, 'store']);
    Route::put('/{id}', [STOCKSController::class, 'update']);
    Route::delete('/{id}', [STOCKSController::class, 'destroy']);
    Route::post('/{stockId}/skus', [STOCKSController::class, 'addSku']);
    Route::delete('/{stockId}/skus/{skuId}', [STOCKSController::class, 'removeSku']);
});
Route::get('/stocks', [STOCKSController::class, 'index']);
Route::get('/stocks/{id}', [STOCKSController::class, 'show']);

// ✅ Stock Logs (Admin Only)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('stock-log')->group(function () {
    Route::post('/', [STOCKLOGController::class, 'store']);
    Route::put('/{id}', [STOCKLOGController::class, 'update']);
    Route::delete('/{id}', [STOCKLOGController::class, 'destroy']);
});
Route::get('/stock-log', [STOCKLOGController::class, 'index']);
Route::get('/stock-log/{id}', [STOCKLOGController::class, 'show']);

// ✅ Employees Management
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('employees')->group(function () {
    Route::post('/', [EMPLOYEESController::class, 'store']);
    Route::put('/{id}', [EMPLOYEESController::class, 'update']);
    Route::delete('/{id}', [EMPLOYEESController::class, 'destroy']);
});
Route::get('/employees', [EMPLOYEESController::class, 'index']);
Route::get('/employees/{id}', [EMPLOYEESController::class, 'show']);

// ✅ Customers Management
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('customers')->group(function () {
    Route::post('/', [CUSTOMERSController::class, 'store']);
    Route::put('/{id}', [CUSTOMERSController::class, 'update']);
    Route::delete('/{id}', [CUSTOMERSController::class, 'destroy']);
});
Route::get('/customers', [CUSTOMERSController::class, 'index']);
Route::get('/customers/{id}', [CUSTOMERSController::class, 'show']);

// ✅ Orders
Route::middleware(['auth:sanctum'])->prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::put('/{id}', [OrderController::class, 'update']);
    Route::delete('/{id}', [OrderController::class, 'destroy']);
});
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show']);

// ✅ Check for Duplicate Stocks
Route::get('/stocks/check-duplicate', function (Request $request) {
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

// ✅ Email Verification Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return response()->json(['message' => 'Email verified successfully.']);
    })->name('verification.verify');

    Route::get('/email/verify-url', function (Request $request) {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify', now()->addMinutes(60), ['id' => $user->id]
        );

        return response()->json(['url' => $verificationUrl]);
    });
});
