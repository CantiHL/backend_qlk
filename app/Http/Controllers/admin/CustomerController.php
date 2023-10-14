<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Location;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_debt($id)
    {
        $customer = Customer::find($id);
        return response()->json($customer, 200);
    }
    public function update_debt(Request $request, $id)
    {
        if ($request) {
            $item = Customer::findOrFail($id);
            $item->debt += $request->debt;
            $item->save();
            return response()->json(['update successful', 200]);
        } else {
            return response()->json(['update faild', 401]);
        }
    }
    public function index()
    {
        $list_customers = Customer::get();
        $list_location = Location::select('id', 'name', 'desc')->get();
        $response = [
            'data' => $list_customers,
            'location' => $list_location,
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
        if ($request) {
            $request->validate(
                [
                    'fullname' => 'required',
                    'address' => 'required',
                ]
            );
            $data = [
                'fullname' => $request->fullname,
                'address' => $request->address,
                'phone' => $request->phone,
                'debt' => $request->debt,
                'location' => $request->location,
            ];
            Customer::create($data);
            return response()->json(['successful ', 201]);
        }
        return response()->json(['faild ', 401]);
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
        $customer = Customer::find($id);
        return response()->json($customer, 200);
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
        $item = Customer::findOrFail($id);
        if ($item != null) {
            $data = $request->all();
            $item = Customer::findOrFail($id);
            $item->update($data);
            return response()->json(['successful ', 200]);
        } else {
            return response()->json(['successful ', 200]);
        }
        return response()->json(['faild ', 401]);
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
