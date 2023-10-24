<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request)
    {
        if (!empty($request)) {
            $group_id = $request->id;
            $products = Products::when(count($group_id) > 0, function ($query) use ($group_id) {
                return $query->whereIn('group', $group_id);
            })->leftJoin('product_groups', 'products.group', '=', 'product_groups.id')
                ->leftJoin('sales_items', 'products.id', '=', 'sales_items.product_id')
                ->leftJoin('purchase_items', 'products.id', '=', 'purchase_items.product_id')
                ->select(
                    'products.id',
                    'products.name',
                    'products.code',
                    'products.buy_price',
                    'products.sell_price',
                    'products.color',
                    'products.stock',
                    'products.active',
                    'product_groups.group_name as product_groups_name',
                    DB::raw('SUM(DISTINCT sales_items.quality) as sell_quality'),
                    DB::raw('SUM(DISTINCT purchase_items.quality) as purchase_quality')
                )
                ->groupBy(
                    'products.id',
                    'products.name',
                    'product_groups.group_name',
                    'products.code',
                    'products.buy_price',
                    'products.sell_price',
                    'products.color',
                    'products.stock',
                    'products.active',
                )
                ->get();
            $response = [
                'products' => $products,
            ];
            return response()->json($response, 200);
        } else {
            return response(['message' => "failed", 401]);
        }
    }
    public function update_status($id)
    {
        $item = Products::findOrFail($id);
        if ($item->active == 1) {
            $item->active -= 1;
            $item->save();
            return response(['message' => "successful", 200]);
        } elseif ($item->active == 0) {
            $item->active += 1;
            $item->save();
            return response(['message' => "successful", 200]);
        } else {
            return response(['message' => "failed", 401]);
        }
    }

    public function index()
    {
        $totalPrice = DB::table('purchase_items')->selectRaw('SUM(price * quality) as total_price')->value('total_price');
        $totalquality = DB::table('purchase_items')->selectRaw('SUM(quality) as total_quality')->value('total_quality');
        $ProductGroup = DB::table('product_groups')->select('id', 'group_name')->get();
        $Warehouse = DB::table('warehouses')->select('id', 'fullname')->get();
        $products = DB::table('products')
            ->leftJoin('product_groups', 'products.group', '=', 'product_groups.id')
            ->select(
                'products.id',
                'products.name',
                'products.code',
                'products.buy_price',
                'products.sell_price',
                'products.color',
                'products.stock',
                'products.active',
                'product_groups.group_name as product_groups_name',
            )
            ->selectSub(function ($query) {
                $query->from('sales_items')
                    ->whereColumn('sales_items.product_id', 'products.id')
                    ->selectRaw('SUM(quality) as sell_quality');
            }, 'sell_quality')
            ->selectSub(function ($query) {
                $query->from('purchase_items')
                    ->whereColumn('purchase_items.product_id', 'products.id')
                    ->selectRaw('SUM(quality) as purchase_quality');
            }, 'purchase_quality')
            ->get();

        $response = [
            'totalPrice' => $totalPrice,
            'totalquality' => $totalquality,
            'products' => $products,
            'Warehouse' => $Warehouse,
            'ProductGroup' => $ProductGroup,
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
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required',
                'code' => 'required|unique:name',
            ],
            [
                'name.required' => 'This field cannot be left blank',
                'code.required' => 'This field cannot be left blank',
            ]
        );
        if (!$request) {
            return response()->json(['created faild', 401]);
        }
        $data = [
            'name' => $request->name,
            'code' => $request->code,
            'buy_price' => $request->buy_price,
            'sell_price' => $request->sell_price,
            'color' => $request->color,
            'group' => $request->group,
        ];
        Products::create($data);
        $response = [
            "create successful products"
        ];
        return response()->json($response, 201);
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
        $item = Products::findOrFail($id);
        $product_group = DB::table('product_groups')->select('id', 'group_name')->get();
        if ($item) {
            $response = [
                'item' => $item,
                'product_group' => $product_group,
            ];
            return response()->json($response, 200);
        }
        return response(['message' => "failed", 401]);
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
        $product = Products::findOrFail($id);
        if ($product) {
            $product->update($request->all());
            return response()->json(["update successful", 200,]);
        } else {
            return response(['message' => "failed", 401]);
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
        $delete = Products::findOrFail($id);
        if ($delete) {
            $delete->delete();
            return response()->json(["Delete successful ", 200]);
        } else {
            return response()->json(['message' => 'faild'], 401);
        }
    }
}
