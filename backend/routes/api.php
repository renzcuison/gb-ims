<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CATEGORIESController;
use App\Http\Controllers\SUPPLIERSController;
use App\Http\Controllers\ITEMSController;
use App\Http\Controllers\INVENTORY_TRANSACTIONSController;
use App\Http\Controllers\STOCKSController;
use App\Http\Controllers\EMPLOYEESController;
use App\Http\Controllers\CUSTOMERSController;
use App\Http\Controllers\TRANSACTION_ITEMSController;
use App\Models\Item;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
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



Route::get('inventory_transactions', [INVENTORY_TRANSACTIONSController::class, 'index']);
Route::post('inventory_transactions', [INVENTORY_TRANSACTIONSController::class, 'store']);
Route::get('inventory_transactions/{id}', [INVENTORY_TRANSACTIONSController::class, 'show']);
Route::put('inventory_transactions/{id}', [INVENTORY_TRANSACTIONSController::class, 'update']);
Route::delete('inventory_transactions/{id}', [INVENTORY_TRANSACTIONSController::class, 'destroy']);

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

Route::get('transaction_items', [TRANSACTION_ITEMSController::class, 'index']);
Route::post('transaction_items', [TRANSACTION_ITEMSController::class, 'store']);
Route::get('transaction_items/{id}', [TRANSACTION_ITEMSController::class, 'show']);
Route::put('transaction_items/{id}', [TRANSACTION_ITEMSController::class, 'update']);
Route::delete('transaction_items/{id}', [TRANSACTION_ITEMSController::class, 'destroy']);
Route::post('/inventory_transactions/{id}/items', [TRANSACTION_ITEMSController::class, 'attachItems']);