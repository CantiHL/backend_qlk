<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sales;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $currentMonth = date('m');
        $totalSales = Sales::join('sales_items', 'sales.id', '=', 'sales_items.sales_id')
            ->whereMonth('sales.created_at', '=', $currentMonth)
            ->selectRaw('SUM(((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100)))-((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100))) * (sales.discount/100)) as total_price')
            ->groupBy('sales_items.sales_id')
            ->get()
            ->pluck('total_price')
            ->sum();
        $totalOrder = DB::table('sales')
            ->whereMonth('sales.created_at', '=', $currentMonth)
            ->select(DB::raw('COUNT(sales.id) as total_orders'))
            ->get()
            ->pluck('total_orders')
            ->first();
        $totalCustomerDebt = DB::table('customers')
            ->select(DB::raw('SUM(customers.debt) as total_debt'))
            ->get()->pluck('total_debt')
            ->first();
        $totalStaffDebt = DB::table('staff')
            ->select(DB::raw('SUM(staff.debt) as total_staffdebt'))
            ->get()
            ->pluck('total_staffdebt')
            ->first();
        $totalPrice = DB::table('purchase_items')
            ->whereMonth('purchase_items.created_at', '=', $currentMonth)
            ->selectRaw('SUM((price * quality) - ((price * quality) * (discount/100))) as total_price')
            ->get()
            ->pluck('total_price')
            ->first();
        $response = [
            'totalSales' => $totalSales,
            'totalOrder' => $totalOrder,
            'totalCustomerDebt' => $totalCustomerDebt,
            'totalStaffDebt' => $totalStaffDebt,
            'totalPrice' => $totalPrice,
        ];
        return response()->json($response, 200);
    }
}
