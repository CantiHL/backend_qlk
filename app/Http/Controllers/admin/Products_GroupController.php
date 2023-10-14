<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product_Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Products_GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $Product_Group = Product_Group::get();
        $response = [
            'data' => $Product_Group,
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
        $check =  $request->validate(
            [
                'group_name' => 'required',
                'group_code' => 'required',
            ],
            [
                'group_name.required' => 'This field cannot be left blank',
                'group_code.required' => 'This field cannot be left blank',
            ]
        );
        if ($check) {
            $data = [
                'group_name' => $request->group_name,
                'group_code' => $request->group_code,
                'description' => $request->description,
                'parent' => $request->parent,
                'commission' => $request->commission,
                'commission_type' => $request->commission_type,
                'commission_target' => $request->commission_target,
            ];
            Product_Group::create($data);
            $response = [
                'message' => "created successful"
            ];
            return response($response, 201);
        } else {
            $response = [
                'message' => "created failed"
            ];
            return response($response, 401);
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
        $item = Product_Group::findOrFail($id);
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
        $product = Product_Group::findOrFail($id);
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
        $id = Product_Group::findOrFail($id);
        if ($id) {
            $id->delete();
            return response()->json(["delete successful", 200,]);
        } else {
            return response(['message' => "failed", 401]);
        }
    }
}
