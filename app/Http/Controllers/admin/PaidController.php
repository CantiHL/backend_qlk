<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Paid;
use App\Models\Sales;
use Illuminate\Http\Request;

class PaidController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $validatedData = $request->validate([
            'sales_id' => 'required',
            'date' => 'required',
            'money' => 'required',
        ]);
        $existingPaid = Paid::where('sales_id', $validatedData['sales_id'])->first();
        $sale = Sales::find($validatedData['sales_id']);
        $customer = Customer::find($sale->customer_id);
        $customer->debt -= $validatedData['money'];
        $customer->save();
        if ($existingPaid) {
            $existingPaid->money += $validatedData['money'];
            $existingPaid->save();
        } else {
            Paid::create([
                'sales_id' => $validatedData['sales_id'],
                'date' => $validatedData['date'],
                'money' => $validatedData['money'],
            ]);
        }
        $response = [
            "create successful"
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
