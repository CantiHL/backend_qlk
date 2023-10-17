<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ImportFileExcel;
use App\Models\Products;
use App\Models\Purchase;
use App\Models\Purchase_item;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PurchasesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function upload_file(Request $request)
    {
        if ($request->hasFile('file')) {
            $chunkSize = 250;
            $file = $request->file('file');
            $data = Excel::toCollection(new ImportFileExcel, $file);
            // $product = Products::where('code', $code)->first();
            foreach ($data->chunk($chunkSize) as $chunk) {
                dd($chunk[0]);
            }

            if (!empty($request)) {
                $data = [
                    'date' => $request->purchases["date"],
                    'warehouse_id' => $request->purchases["warehouse_id"],
                    'note' => "",
                    'status' => 0,
                ];
                $purchase = Purchase::create($data);
                $purchase->save();
                $purchase_id = $purchase->id;
            }

            foreach ($request->purchases_item as $item) {
                $purchases_item = new Purchase_item([
                    'purchases_id' => $purchase_id,
                    'product_id' => $item["product_id"],
                    'quality' => $item["quality"],
                    'get_more' => $item["get_more"],
                    'discount' => $item["discount"],
                    'price' => $item["price"],
                ]);
                $purchases_item->save();
            }
        }
    }
    public function filter(Request $request)
    {
        try {
            $result = DB::table('purchases')
                ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchases_id')
                ->join('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
                ->select(
                    'purchases.*',
                    DB::raw('SUM((purchase_items.price * purchase_items.quality - ((purchase_items.price * purchase_items.quality ) * (purchase_items.discount / 100)))) as total_price'),
                    DB::raw('SUM(purchase_items.quality) as total_quality'),
                    'purchases.warehouse_id',
                    'warehouses.fullname as warehouse_name',
                )
                ->groupBy('purchases.id', 'purchases.date', 'purchases.status', 'purchases.note', 'purchases.warehouse_id', 'purchases.created_at', 'purchases.updated_at', 'purchases.warehouse_id', 'warehouses.fullname')
                ->when($request->from_date, function ($query) use ($request) {
                    return $query->where('purchases.date', '>=', $request->from_date);
                })
                ->when($request->to_date, function ($query) use ($request) {
                    return $query->where('purchases.date', '<=', $request->to_date);
                })->get();
            $response = [
                "list_item" => $result
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve items', 'error' => $e->getMessage()], 500);
        }
    }
    public function getPurchasesDetail($id)
    {
        $date = DB::table('purchases')->where('id', $id)->select('date')->first();
        $purchases_detail = DB::table('purchase_items')
            ->where('purchases_id', $id)
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->select('purchase_items.*', 'products.name', 'products.code')
            ->get();
        if (!$purchases_detail) {
            return response()->json(['get faild', 401]);
        }
        $response = [
            'created_at' => $date,
            'purchases_detail' => $purchases_detail,
        ];
        return response()->json($response, 200);
    }
    public function list_purchases()
    {
        $totalPrice = DB::table('purchase_items')->selectRaw('SUM((price * quality) - ((price * quality) * (discount/100))) as total_price')->get()->pluck('total_price')->first();
        $result = DB::table('purchases')
            ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchases_id')
            ->join('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
            ->select(
                'purchases.*',
                DB::raw('SUM((purchase_items.price * purchase_items.quality - ((purchase_items.price * purchase_items.quality ) * (purchase_items.discount / 100)))) as total_price'),
                DB::raw('SUM(purchase_items.quality) as total_quality'),
                'purchases.warehouse_id',
                'warehouses.fullname as warehouse_name',
            )
            ->groupBy('purchases.id', 'purchases.date', 'purchases.status', 'purchases.note', 'purchases.warehouse_id', 'purchases.created_at', 'purchases.updated_at', 'purchases.warehouse_id', 'warehouses.fullname')
            ->get();
        if (!$result && !$totalPrice) {
            return response()->json(['get faild', 401]);
        }
        $response = [
            'purchases' => $result,
            'totalPrice' => $totalPrice,
        ];
        return response()->json($response, 200);
    }
    public function index()
    {
        $products = Products::where('active', 1)->get();
        $warehouse = Warehouse::select('id', 'fullname')->get();
        $response = [
            'products' => $products,
            'warehouse' => $warehouse
        ];
        return response()->json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
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
            'purchases_item.*.product_id' => 'required|integer',
            'purchases_item.*.code' => 'required|string',
            'purchases_item.*.name' => 'required|string',
            'purchases_item.*.price' => 'required|integer',
            'purchases_item.*.quality' => 'required|integer',
            'purchases_item.*.get_more' => 'required|integer',
            'purchases_item.*.discount' => 'required|integer',
            'purchases.date' => 'required|date',
            'purchases.warehouse_id' => 'required|integer',
            'purchases.status' => 'required|integer',
        ]);

        if ($request) {
            $data = [
                'date' => $request->purchases["date"],
                'warehouse_id' => $request->purchases["warehouse_id"],
                'note' => $request->purchases["note"],
                'status' => $request->purchases["status"],
            ];
            $purchase = Purchase::create($data);
            $purchase->save();
            $purchase_id = $purchase->id;
            foreach ($request->purchases_item as $item) {
                $purchases_item = new Purchase_item([
                    'purchases_id' => $purchase_id,
                    'product_id' => $item["product_id"],
                    'quality' => $item["quality"],
                    'get_more' => $item["get_more"],
                    'discount' => $item["discount"],
                    'price' => $item["price"],
                ]);
                $purchases_item->save();
            }
            return response()->json(['create successful', 201]);
        }
        return response()->json(['create faild', 401]);
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
        $purchase = Purchase::find($id);
        $purchases_item = DB::table('purchase_items')
            ->where('purchases_id', $id)
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->select('purchase_items.*', 'products.name', 'products.code')
            ->get();
        if (!$purchases_item) {
            return response()->json(['get faild', 401]);
        }
        $response = [
            'purchases_detail' => $purchases_item,
            'purchase' => $purchase,
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
            'purchases_item.*.product_id' => 'required|integer',
            'purchases_item.*.code' => 'required|string',
            'purchases_item.*.name' => 'required|string',
            'purchases_item.*.price' => 'required|integer',
            'purchases_item.*.quality' => 'required|integer',
            'purchases_item.*.get_more' => 'required|integer',
            'purchases_item.*.discount' => 'required|integer',
            'purchases.date' => 'required|date',
            'purchases.warehouse_id' => 'required|integer',
            'purchases.status' => 'required|integer',
        ]);
        $purchases_id = Purchase_item::where('purchases_id', $id)->get();
        $ProductIds = $purchases_id->pluck('product_id')->toArray();
        if ($request) {
            Purchase::where('id', $id)
                ->update([
                    'date' => $request->purchases["date"],
                    'warehouse_id' => $request->purchases["warehouse_id"],
                    'note' => $request->purchases["note"],
                    'status' => $request->purchases["status"],
                ]);
            foreach ($request->purchases_item as $item) {
                Purchase_item::updateOrInsert(
                    [
                        'purchases_id' => $id,
                        'product_id' => $item["product_id"],
                    ],
                    [
                        'quality' => $item["quality"],
                        'get_more' => $item["get_more"],
                        'discount' => $item["discount"],
                        'price' => $item["price"],
                    ]
                );
                $key = array_search($item["product_id"], $ProductIds);
                if ($key !== false) {
                    unset($ProductIds[$key]);
                }
            }
            Purchase_item::whereIn('product_id', $ProductIds)->delete();
            return response()->json(['update successful', 201]);
        }
        return response()->json(['update faild', 401]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = Purchase::findOrFail($id);
        if ($delete) {
            $delete->delete();
            return response()->json(["Delete successful ", 200]);
        } else {
            return response()->json(['message' => 'faild'], 401);
        }
    }
}
