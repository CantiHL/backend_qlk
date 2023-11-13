<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ImportFileExcel;
use App\Models\Customer;
use App\Models\GuaranteeItem;
use App\Models\Products;
use App\Models\Purchase_item;
use App\Models\Sales;
use App\Models\Sales_item;
use App\Models\Staff;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function upload_file(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv|max:51200',
        ]);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data = Excel::toCollection(new ImportFileExcel, $file)->first();
            if ($data->count() > 0) {
                $data->shift();
                $sale = Sales::create([
                    'user_id' => auth()->id(),
                    'staff_id' => $request->staff_id,
                    'customer_id' => $request->customer_id,
                    'warehouse_id' => $request->warehouse_id,
                    'date' => $request->date,
                    'note' => $request->note ? $request->note : null,
                ]);
                $sale_id = $sale->id;
                foreach ($data as $row) {
                    $product = Products::where('code', $row[0])->first();
                    if ($product && $product->stock >= $row[3]) {
                        $saleItem = new Sales_item([
                            'sales_id' => $sale_id,
                            'product_id' => $product->id,
                            'quality' => $row[3],
                            'get_more' => $row[4],
                            'discount' => $row[5],
                            'price' => $row[6],
                        ]);
                        $product->stock = $product->stock - $row[3];
                        $product->save();
                        $saleItem->save();
                    }
                }
                return response()->json(['message' => 'upload file successful'], 201);
            }
        } else {
            return response()->json(['message' => 'upload file Failed'], 400);
        }
    }
    public function getSalesBill($id)
    {
        $sales_bill = DB::table('sales')
            ->where('sales.trash', 0)
            ->where('sales.id', $id)
            ->join('sales_items', 'sales.id', '=', 'sales_items.sales_id')
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->select('sales_items.*', 'sales.staff_id', 'products.name', 'products.code')
            ->get();
        $sales_staff = DB::table('sales')
            ->where('sales.trash', 0)
            ->join('staff', 'sales.staff_id', '=', 'staff.id')
            ->select('staff.fullname as name_staff', 'staff.phone as phone_staff')
            ->where('sales.id', $id)
            ->first();
        $customer = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.trash', 0)
            ->select('customers.fullname as name_customers', 'customers.phone as phone_customers', 'customers.address as address_customers')
            ->where('sales.id', $id)
            ->first();
        $date = DB::table('sales')
            ->where('sales.id', $id)->where('sales.trash', 0)->select('date', 'id', 'discount')->get()->first();

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
            ->where('sales.trash', 0)
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
                'paids.money',
                'sales.trash'
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
        $purchase_id = DB::table('purchases')
            ->where('warehouse_id', function ($query) {
                $query->select(DB::raw('max(warehouse_id)'))
                    ->from('purchases');
            })
            ->pluck('id');
        $purchaseItems = Purchase_item::whereIn('purchases_id', $purchase_id->all())->pluck('product_id');
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
        $totalSales = Sales::join('sales_items', 'sales.id', '=', 'sales_items.sales_id')->where('sales.trash', 0)
            ->selectRaw('SUM(((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100)))-((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100))) * (sales.discount/100)) as total_price')
            ->groupBy('sales_items.sales_id')
            ->get()
            ->pluck('total_price')
            ->sum();
        $sales_paid = DB::table('sales')
            ->where('status', 1)
            ->where('sales.trash', 0)
            ->join('sales_items', 'sales.id', '=', 'sales_items.sales_id')
            ->selectRaw('SUM(((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100)))-((sales_items.price * sales_items.quality) - ((sales_items.price * sales_items.quality) * (sales_items.discount/100))) * (sales.discount/100)) as unpaid')
            ->pluck('unpaid')
            ->sum();
        $paids = DB::table('sales')
            ->where('sales.trash', 0)
            ->where('status', 0)
            ->join('paids', 'sales.id', '=', 'paids.sales_id')
            ->select('money')->sum('money');
        $sales_list = DB::table('sales')
            ->where('sales.trash', 0)
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
                'paids.money',
                'sales.trash'
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
        // dd($request->sales);

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
            $customer = Customer::find($request->sales["customer_id"]);
            if ($request->sales["status"] == 0) {
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
                    //update debt customer
                    $customer->debt = $customer->debt + (($item["price"] * $item["quality"] - $item["price"] * $item["quality"] * $item["discount"] * 0.01) - ($item["price"] * $item["quality"] * $item["get_more"] * 0.01));
                    $customer->save();
                    if ($sales_item->guarantee > 0) {
                        GuaranteeItem::create([
                            'sale_id' => $sales_id,
                            'product_id' => $item["product_id"],
                            'quantity' => $item["guarantee"],
                        ]);
                    }
                }
            } else {
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
                    if ($sales_item->guarantee > 0) {
                        GuaranteeItem::create([
                            'sale_id' => $sales_id,
                            'product_id' => $item["product_id"],
                            'quantity' => $item["guarantee"],
                        ]);
                    }
                }
            }
            // giảm số lượng của nhập hàng nếu cần
            // $purchase_item = Purchase_item::where('product_id', $item["product_id"])->first();
            // $purchase_item->quality -= $item["quality"];
            // $purchase_item->save();
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
        return response()->json(['delete successful', 200]);
    }
    public function trash($id)
    {
        $delete = Sales::where('id', $id)->update([
            'trash' => 1
        ]);
        if ($delete) {
            return response()->json(["Delete successful ", 200]);
        } else {
            return response()->json(['message' => 'faild'], 401);
        }
    }

    // public function exportSale(Request $request)
    // {
    //     $condition = [
    //         'date' => $request->date,
    //         'date' => $request->date,
    //     ];
    // }
}
