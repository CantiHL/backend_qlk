<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product_Group;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WareHouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Warehouse = Warehouse::get();
        $response = [
            'data' => $Warehouse,
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
                'fullname' => 'required',
                'address' => 'required',
            ],
            [
                'fullname.required' => 'This field cannot be left blank',
                'address.required' => 'This field cannot be left blank',
            ]
        );
        if ($request) {
            $data = [
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'address' => $request->address,
            ];
            Warehouse::create($data);
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
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Warehouse::findOrFail($id);
        if ($item) {
            $response = [
                'item' => $item,
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
        if ($request != null) {
            $data = $request->all();
            $item = Warehouse::findOrFail($id);
            $item->update($data);
            $response = [
                'message' => "update warehouse successful"
            ];
            return response($response, 201);
        } else {
            $response = [
                'message' => "update warehouse  failed"
            ];
            return response($response, 401);
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
        $id = Warehouse::findOrFail($id);
        if ($id) {
            $id->delete();
            return response()->json(["Delete successful ", 200]);
        } else {
            return response()->json(['message' => 'faild'], 401);
        }
    }
}
