<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sales;
use App\Models\Staff;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_order($id)
    {
        $staff = Staff::select('id', 'fullname')->get();
        $fakeDataStaff = new Staff([
            'fullname' => 'Tất cả',
        ]);
        $staff->prepend($fakeDataStaff);
        $customers = Customer::select('id', 'fullname', 'address')->get();
        $fakeDatacustomers = new Customer([
            'fullname' => 'Tất cả',
        ]);
        $customers->prepend($fakeDatacustomers);
        $warehouses = Warehouse::select('id', 'fullname', 'address')->get();
        $totalSales = Sales::join('sales_items', 'sales.id', '=', 'sales_items.sales_id')
            ->where('customer_id', $id)
            ->selectRaw('SUM(((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100)))-((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100))) * (sales.discount/100)) as total_price')
            ->groupBy('sales_items.sales_id')
            ->get()
            ->pluck('total_price')
            ->sum();
        $sales_paid = DB::table('sales')
            ->where('customer_id', $id)
            ->where('status', 1)
            ->join('sales_items', 'sales.id', '=', 'sales_items.sales_id')
            ->selectRaw('SUM(((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100)))-((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100))) * (sales.discount/100)) as unpaid')
            ->pluck('unpaid')
            ->sum();
        $paids = DB::table('sales')
            ->where('customer_id', $id)
            ->where('status', 0)
            ->join('paids', 'sales.id', '=', 'paids.sales_id')
            ->select('money')->sum('money');
        // get
        $sales_list = DB::table('sales')
            ->where('customer_id', $id)
            ->join('sales_items', 'sales.id', '=', 'sales_items.sales_id')
            ->leftJoin('paids', 'sales.id', '=', 'paids.sales_id')
            ->join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
            ->join('staff', 'sales.staff_id', '=', 'staff.id')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->select(
                'sales.*',
                DB::raw('SUM((sales_items.price * sales_items.quality - ((sales_items.price * sales_items.quality ) * (sales_items.discount / 100)))) as total_price'),
                'sales.warehouse_id',
                'warehouses.fullname as warehouse_name',
                'staff.fullname as staff_name',
                'customers.fullname as customer_name',
                'paids.money as paid_money'
            )
            ->groupBy(
                'sales.id',
                'sales.date',
                'sales.status',
                'sales.note',
                'sales.warehouse_id',
                'sales.created_at',
                'sales.updated_at',
                'sales.warehouse_id',
                'warehouses.fullname',
                'staff.fullname',
                'customers.fullname',
                'sales.user_id',
                'sales.staff_id',
                'sales.customer_id',
                'sales.discount',
                'sales.debt',
                'paids.money'
            )
            ->get();
        $total_paids = $sales_paid + $paids;
        if (!$sales_list) {
            return response()->json(['get faild', 401]);
        }
        $response = [
            'totalSales' => $totalSales,
            'total_paids' => $total_paids,
            'sales' => $sales_list,
            'customers' => $customers,
            'staff' => $staff,
            'warehouses' => $warehouses,
        ];
        return response()->json($response, 200);
    }
    public function getListProductsOrder($id)
    {
        $listProductsOrder = DB::table('sales')
            ->where('customer_id', $id)
            ->where('status', 1)
            ->join('sales_items', 'sales.id', '=', 'sales_items.sales_id')
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.code',
                DB::raw('SUM(sales_items.quality) as total_quality'),
                DB::raw('MAX(sales.date) as date')
            )
            ->groupBy('products.id', 'products.name', 'products.code')
            ->get();
        if (!$listProductsOrder) {
            return response()->json(['get faild', 401]);
        }
        $response = [
            'list_productOrder' => $listProductsOrder,
        ];
        return response()->json($response, 200);
    }
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
