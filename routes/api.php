<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\OrderCustomerController;
use App\Http\Controllers\Admin\PaidController;
use App\Http\Controllers\Admin\Products_GroupController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\PurchasesController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StatisticalController;
use App\Http\Controllers\Admin\TargetController;
use App\Http\Controllers\Admin\Warehouse_TransferController;
use App\Http\Controllers\Admin\WareHouseController;
use App\Http\Controllers\Admin\TargetPurchaseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'productgroup'], function () {
        Route::get('/list', [Products_GroupController::class, 'index']);
        Route::post('/create', [Products_GroupController::class, 'store']);
        Route::get('/edit/{id}', [Products_GroupController::class, 'edit']);
        Route::put('/update/{id}', [Products_GroupController::class, 'update']);
        Route::delete('/delete/{id}', [Products_GroupController::class, 'destroy']);
    });
    Route::group(['prefix' => 'products'], function () {
        Route::get('/list', [ProductsController::class, 'index']);
        Route::post('/list', [ProductsController::class, 'index']);
        Route::post('/create', [ProductsController::class, 'store']);
        Route::get('/edit/{id}', [ProductsController::class, 'edit']);
        Route::put('/update/{id}', [ProductsController::class, 'update']);
        Route::delete('/delete/{id}', [ProductsController::class, 'destroy']);
        Route::post('/filter', [ProductsController::class, 'filter']);
        Route::put('/status/{id}', [ProductsController::class, 'update_status']);
    });

    Route::group(['prefix' => 'warehouse'], function () {
        Route::get('/list', [WareHouseController::class, 'index']);
        Route::post('/create', [WareHouseController::class, 'store']);
        Route::get('/edit/{id}', [WareHouseController::class, 'edit']);
        Route::put('/update/{id}', [WareHouseController::class, 'update']);
        Route::delete('/delete/{id}', [WareHouseController::class, 'destroy']);
        Route::post('/transfer-products', [Warehouse_TransferController::class, 'warehouseTransferProduct']);
    });
    Route::group(['prefix' => 'discount'], function () {
        Route::get('/list', [DiscountController::class, 'index']);
        Route::post('/create', [DiscountController::class, 'store']);
        Route::get('/edit/{id}', [DiscountController::class, 'edit']);
        Route::delete('/delete/{id}', [DiscountController::class, 'destroy']);
        Route::post('/filter', [DiscountController::class, 'filter']);
        Route::get('/get/item/{id}', [DiscountController::class, 'get_discount']);
        Route::put('/update/item/{id}', [DiscountController::class, 'update_discount']);
    });

    Route::group(['prefix' => 'staff'], function () {
        Route::get('/list', [StaffController::class, 'index']);
        Route::post('/create', [StaffController::class, 'store']);
        Route::get('/edit/{id}', [StaffController::class, 'edit']);
        Route::delete('/delete/{id}', [StaffController::class, 'destroy']);
        Route::put('/update/{id}', [StaffController::class, 'update']);
        Route::get('/get/debt/{id}', [StaffController::class, 'get_debt']);
        Route::put('/update/debt/{id}', [StaffController::class, 'update_debt']);
        Route::put('/update/status/{id}', [StaffController::class, 'update_status']);
    });

    Route::group(['prefix' => 'location'], function () {
        Route::get('/list', [LocationController::class, 'index']);
        Route::post('/create', [LocationController::class, 'store']);
        Route::get('/edit/{id}', [LocationController::class, 'edit']);
        Route::put('/update/{id}', [LocationController::class, 'update']);
        Route::delete('/delete/{id}', [LocationController::class, 'destroy']);
    });
    Route::group(['prefix' => 'customers'], function () {
        Route::get('/list', [CustomerController::class, 'index']);
        Route::post('/create', [CustomerController::class, 'store']);
        Route::get('/edit/{id}', [CustomerController::class, 'edit']);
        Route::put('/update/{id}', [CustomerController::class, 'update']);
        Route::delete('/delete/{id}', [CustomerController::class, 'destroy']);
        Route::get('/get/debt/{id}', [CustomerController::class, 'get_debt']);
        Route::put('/update/debt/{id}', [CustomerController::class, 'update_debt']);
        Route::get('order/{id}', [OrderCustomerController::class, 'get_order']);
        Route::get('/products/order/{id}', [OrderCustomerController::class, 'getListProductsOrder']);
        Route::post('/upload/file', [CustomerController::class, 'upload_file']);

    });

    Route::group(['prefix' => 'purchases'], function () {
        Route::get('/get', [PurchasesController::class, 'index']);
        Route::post('/create', [PurchasesController::class, 'store']);
        Route::get('/edit/{id}', [PurchasesController::class, 'edit']);
        Route::put('/update/{id}', [PurchasesController::class, 'update']);
        Route::delete('/delete/{id}', [PurchasesController::class, 'destroy']);
        Route::delete('/trash/{id}', [PurchasesController::class, 'trash']);
        Route::post('/filter', [PurchasesController::class, 'filter']);
        Route::get('/list', [PurchasesController::class, 'list_purchases']);
        Route::post('/upload/file', [PurchasesController::class, 'upload_file']);
        Route::get('/detail/{id}', [PurchasesController::class, 'getPurchasesDetail']);
    });
    Route::group(['prefix' => 'sales'], function () {
        Route::get('/list', [SalesController::class, 'index']);
        Route::get('/get/create', [SalesController::class, 'getCreate']);
        Route::post('/create', [SalesController::class, 'store']);
        Route::get('/edit/{id}', [SalesController::class, 'edit']);
        Route::put('/update/{id}', [SalesController::class, 'update']);
        Route::delete('/delete/{id}', [SalesController::class, 'destroy']);
        Route::delete('/trash/{id}', [SalesController::class, 'trash']);
        Route::post('/create/paid', [PaidController::class, 'store']);
        Route::put('/update/status/{id}', [SalesController::class, 'update_status']);
        Route::post('/get/bill/{id}', [SalesController::class, 'getSalesBill']);
        Route::post('/filter', [SalesController::class, 'filter_total']);
        Route::post('/filter/products/{id}', [SalesController::class, 'filter_products']);
        Route::post('/upload/file', [SalesController::class, 'upload_file']);
    });
    Route::group(['prefix' => 'statistical'], function () {
        Route::get('/staff-salary', [StatisticalController::class, 'salerSalary']);
        Route::post('/staff-salary', [StatisticalController::class, 'salerSalary']);
        Route::get('/discount-report', [StatisticalController::class, 'discountReport']);
        Route::post('/discount-report', [StatisticalController::class, 'discountReport']);
        Route::get('/import-sales', [StatisticalController::class, 'importSales']);
        Route::post('/import-sales', [StatisticalController::class, 'importSales']);
        Route::get('/guarantee-product', [StatisticalController::class, 'guaranteeProduct']);
        Route::post('/guarantee-product', [StatisticalController::class, 'guaranteeProduct']);
        Route::get('/real-sales', [StatisticalController::class, 'realSales']);
        Route::post('/real-sales', [StatisticalController::class, 'realSales']);
        Route::get('/pay-request', [StatisticalController::class, 'payRequest']);
    });
    Route::group(['prefix' => 'target'], function () {
        Route::get('/target-purchase', [TargetPurchaseController::class, 'index']);
        Route::post('/target-purchase-create', [TargetPurchaseController::class, 'create']);
        Route::get('/target', [TargetController::class, 'index']);
        Route::post('/target', [TargetController::class, 'index']);
        Route::post('/target-create', [TargetController::class, 'create']);
    });
});
Route::post('/logout', [AuthController::class, 'logout']);