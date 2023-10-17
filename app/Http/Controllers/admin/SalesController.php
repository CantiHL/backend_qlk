<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Products;
use App\Models\Purchase;
use App\Models\Purchase_item;
use App\Models\Sales;
use App\Models\Sales_item;
use App\Models\Staff;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSalesBill($id)
    {
        $sales_bill = DB::table('sales')
            ->where('sales.id', $id)
            ->join('sales_items', 'sales.id', '=', 'sales_items.sales_id')
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->select('sales_items.*', 'sales.staff_id', 'products.name', 'products.code')
            ->get();
        $sales_staff = DB::table('sales')
            ->join('staff', 'sales.staff_id', '=', 'staff.id')
            ->select('staff.fullname as name_staff', 'staff.phone as phone_staff')
            ->where('sales.id', $id)
            ->first();
        $customer = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->select('customers.fullname as name_customers', 'customers.phone as phone_customers', 'customers.address as address_customers')
            ->where('sales.id', $id)
            ->first();
        $date = DB::table('sales')
            ->where('sales.id', $id)->select('date', 'id', 'discount')->get()->first();

        if (!$sales_bill && !$sales_staff && !$customer) {
            return response()->json(['get faild', 401]);
        }
        $response = [
            'created_at' => $date,
            'sales_staff' => $sales_staff,
            'customer' => $customer,
            'sales_bill' => $sales_bill,
        ];
        return response()->json($response, 200);
    }
    public function filter_total(Request $request)
    {
        $filter_list = DB::table('sales')
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
            ->when($request->from_date, function ($query) use ($request) {
                return $query->where('sales.date', '>=', $request->from_date);
            })
            ->when($request->to_date, function ($query) use ($request) {
                return $query->where('sales.date', '<=', $request->to_date);
            })
            ->when($request->staff_id, function ($query) use ($request) {
                return $query->where('sales.staff_id', $request->staff_id);
            })
            ->when($request->customer_id, function ($query) use ($request) {
                return $query->where('sales.customer_id', $request->customer_id);
            })
            ->when($request->status_id !== null, function ($query) use ($request) {
                return $query->where('sales.status', $request->status_id);
            })
            ->get();
        $response = [
            'filter_list' => $filter_list,
        ];
        return response()->json($response, 200);
    }
    public function filter_products($id)
    {
        $products_filter = DB::table('purchases')
            ->where('warehouse_id', $id)
            ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchases_id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->select('products.*')
            ->get();
        if (!$products_filter) {
            return response()->json(['filter faild'], 401);
        }
        $response = [
            'messange' => 'filter successful',
            'products' => $products_filter,
        ];
        return response()->json($response, 200);
    }
    public function getCreate()
    {
        $staff = Staff::select('id', 'fullname')->get();
        $customers = Customer::select('id', 'fullname', 'address')->get();
        $warehouses = Warehouse::select('id', 'fullname')->get();
        $purchases_id = DB::table('purchases')
            ->where('warehouse_id', function ($query) {
                $query->select(DB::raw('max(warehouse_id)'))
                    ->from('purchases');
            })
            ->pluck('id');
        $purchaseItems = DB::table('purchase_items')
            ->where('purchases_id', $purchases_id)
            ->pluck('product_id');
        $products = Products::whereIn('id', $purchaseItems)->get();

        $response = [
            'staff' => $staff,
            'customers' => $customers,
            'warehouses' => $warehouses,
            'products' => $products,
        ];
        return response()->json($response, 200);
    }
    public function update_status($id)
    {
        $sales = Sales::findOrFail($id);
        if (!$sales) {
            return response()->json(['update faild', 401]);
        }
        if ($sales->status == 0) {
            $sales->status += 1;
            $sales->save();
            $response = [
                'message' => 'Đơn hàng đã được hoàn thành',
            ];
            return response()->json($response, 200);
        } elseif ($sales->status == 1) {
            $sales->status -= 1;
            $sales->save();
            $response = [
                'message' => 'Hủy thanh toán thành công',
            ];
            return response()->json($response, 200);
        }
    }
    public function index()
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
            ->selectRaw('SUM(((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100)))-((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100))) * (sales.discount/100)) as total_price')
            ->groupBy('sales_items.sales_id')
            ->get()
            ->pluck('total_price')
            ->sum();
        $sales_paid = DB::table('sales')
            ->where('status', 1)
            ->join('sales_items', 'sales.id', '=', 'sales_items.sales_id')
            ->selectRaw('SUM(((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100)))-((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100))) * (sales.discount/100)) as unpaid')
            ->pluck('unpaid')
            ->sum();
        $paids = DB::table('sales')
            ->where('status', 0)
            ->join('paids', 'sales.id', '=', 'paids.sales_id')
            ->select('money')->sum('money');
        $sales_list = DB::table('sales')
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
            'sales' => $sales_list,
            'customers' => $customers,
            'staff' => $staff,
            'warehouses' => $warehouses,
            'total_paids' => $total_paids
        ];
        return response()->json($response, 200);
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

        $request->validate([
            'sales_item.*.product_id' => 'required',
            'sales_item.*.quality' => 'required',
            'sales_item.*.get_more' => 'required',
            'sales_item.*.discount' => 'required',
            'sales_item.*.price' => 'required',
            'sales_item.*.guarantee' => 'required',
            'sales.user_id' => 'required',
            'sales.customer_id' => 'required',
            'sales.staff_id' => 'required',
            'sales.date' => 'required',
        ]);
        try {
            // Kiểm tra quality
            foreach ($request->sales_item as $product) {
                $purchase_item = Purchase_item::where('product_id', $product["product_id"])->sum('quality');
                $sales_items = Sales_item::where('product_id', $product["product_id"])->sum('quality');
                if (!$purchase_item && !$sales_items || $purchase_item - $sales_items < $product["quality"]) {
                    $product_name = Products::find($product["product_id"])->name;
                    $response = [
                        'message' => 'Sản phẩm không đủ số lượng để bán',
                        'product_name' => $product_name,
                        'quality' => $purchase_item - $sales_items
                    ];
                    return response()->json($response, 400);
                    break;
                }
            }
            // create Sales
            $data = [
                'user_id' => $request->sales["user_id"],
                'customer_id' => $request->sales["customer_id"],
                'staff_id' => $request->sales["staff_id"],
                'warehouse_id' => $request->sales["warehouse_id"],
                'date' => $request->sales["date"],
                'note' => $request->sales["note"],
                'status' => $request->sales["status"],
                'discount' => $request->sales["discount"],
            ];
            $sales = Sales::create($data);
            $sales->save();
            $sales_id = $sales->id;
            // create Sales_item
            foreach ($request->sales_item as $item) {
                $sales_item = new Sales_item([
                    'sales_id' => $sales_id,
                    'product_id' => $item["product_id"],
                    'quality' => $item["quality"],
                    'get_more' => $item["get_more"],
                    'discount' => $item["discount"],
                    'guarantee' => $item["guarantee"],
                    'price' => $item["price"],
                ]);
                $sales_item->save();
                // giảm số lượng của nhập hàng nếu cần
                // $purchase_item = Purchase_item::where('product_id', $item["product_id"])->first();
                // $purchase_item->quality -= $item["quality"];
                // $purchase_item->save();
            }
            return response()->json(['create successful', 201]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Create failed', 'error' => $e->getMessage()], 401);
        }
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

        $Sales = Sales::find($id);
        $sales_items = DB::table('sales_items')
            ->where('sales_id', $id)
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->select('sales_items.*', 'products.name', 'products.code')
            ->get();
        if (!$sales_items) {
            return response()->json(['get faild', 401]);
        }
        $response = [
            'sales_items' => $sales_items,
            'Sales' => $Sales,
        ];
        return response()->json($response, 200);
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
        $request->validate([
            'sales_item.*.product_id' => 'required',
            'sales_item.*.quality' => 'required',
            'sales_item.*.get_more' => 'required',
            'sales_item.*.discount' => 'required',
            'sales_item.*.price' => 'required',
            'sales_item.*.guarantee' => 'required',
            'sales.user_id' => 'required',
            'sales.customer_id' => 'required',
            'sales.staff_id' => 'required',
            'sales.date' => 'required',
        ]);
        try {
            // check quality 
            foreach ($request->sales_item as $product) {
                $product_name = Products::find($product["product_id"])->name;
                $purchase_item = Purchase_item::where('product_id', $product["product_id"])->sum('quality');
                $sales_items = Sales_item::where('product_id', $product["product_id"])->sum('quality');
                $total_sales_items = Sales_item::where('product_id', $product["product_id"])->first();
                if (!$total_sales_items && !$purchase_item && !$sales_items || $total_sales_items->quality + $purchase_item - $sales_items < $product["quality"]) {
                    $response = [
                        'message' => 'Sản phẩm không đủ số lượng để bán',
                        'product_name' => $product_name,
                        'quality' => $purchase_item - $sales_items
                    ];
                    return response()->json($response, 400);
                    break;
                }
            }
            // update Sales
            Sales::where('id', $id)
                ->update([
                    'user_id' => $request->sales["user_id"],
                    'customer_id' => $request->sales["customer_id"],
                    'staff_id' => $request->sales["staff_id"],
                    'warehouse_id' => $request->sales["warehouse_id"],
                    'date' => $request->sales["date"],
                    'note' => $request->sales["note"],
                    'status' => $request->sales["status"],
                    'discount' => $request->sales["discount"],
                ]);
            // update Sales_item
            // $sales_item = Sales_item::where('sales_id', $id)->get();
            // $ProductIds = $sales_item->pluck('product_id')->toArray();
            foreach ($request->sales_item as $item) {
                Sales_item::updateOrInsert(
                    [
                        'sales_id' => $id,
                        'product_id' => $item["product_id"],
                    ],
                    [
                        'quality' => $item["quality"],
                        'get_more' => $item["get_more"],
                        'discount' => $item["discount"],
                        'guarantee' => $item["guarantee"],
                        'price' => $item["price"],
                    ]
                );
                // giảm số lượng của nhập hàng nếu cần
                // $purchase_item = Purchase_item::where('product_id', $item["product_id"])->first();
                // $purchase_item->quality -= $item["quality"];
                // $purchase_item->save();
                // $key = array_search($item["product_id"], $ProductIds);
                // if ($key !== false) {
                //     unset($ProductIds[$key]);
                // }
            }
            // Sales_item::whereIn('product_id', $ProductIds)->delete();
            return response()->json(['update successful', 201]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Create failed', 'error' => $e->getMessage()], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = Sales::find($id);
        if ($delete) {
            $delete->delete();
            return response()->json(['delete successful', 200]);
        }
    }
}
