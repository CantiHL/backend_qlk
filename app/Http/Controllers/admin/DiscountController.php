<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function get_discount($id)
    {
        $item_discount = Discount::findOrFail($id);
        $products = Products::findOrFail($item_discount->product_id);
        $response = [
            "name" => $products->name,
            "item_discount" => $item_discount,
        ];
        return response()->json($response, 200);
    }
    public function update_discount(Request $request, $id)
    {
        if ($request) {
            $item = Discount::findOrFail($id);
            $updates = [
                'discount' => $request->discount,
                'get_more' => $request->get_more,
                'inv_condition' => $request->inv_condition,
            ];
            $item->update($updates);
            return response()->json(['message' => 'successful'], 200);
        } else {
            return response()->json(['message' => 'faild'], 401);
        }
    }
    public function filter(Request $request)
    {
        $list_discount = Discount::select('discounts.*', 'products.code', 'products.name')
            ->join('products', 'discounts.product_id', '=', 'products.id')
            ->whereBetween('discounts.created_at', [$request->from_date, $request->to_date])
            ->get();
        if ($list_discount) {
            $response = [
                "list_discount" => $list_discount
            ];
            return response()->json($response, 200);
        }
        return response()->json(['message' => 'faild'], 401);
    }

    public function index()
    {
        $products = Products::where('active', 1)->select('id', 'name', 'code')->get();
        $list_discount = Discount::select('discounts.*', 'products.code', 'products.name')
            ->join('products', 'discounts.product_id', '=', 'products.id')
            ->get();
        if ($products) {
            $response = [
                'products' => $products,
                'list_discount' => $list_discount
            ];
            return response()->json($response, 200);
        } else {
            return response()->json(['message' => 'faild'], 401);
        }
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
            'product_id' => 'required',
        ]);
        foreach ($request->product_id as $id) {
            $data = [
                'product_id' => $id,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'discount' => $request->discount,
                'get_more' => $request->get_more,
                'inv_condition' => $request->inv_condition,
            ];
            Discount::create($data);
        }
        return response()->json(['create successful', 201]);
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
        $item = Discount::findOrFail($id);
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
        $id = Discount::findOrFail($id);
        if ($id) {
            $id->delete();
            return response()->json(['delete successful', 200]);
        } else {
            return response()->json(['delete faild', 401]);
        }
    }
}
