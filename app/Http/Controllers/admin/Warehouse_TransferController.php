<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\Warehouse;
use App\Models\Warehouse_transfer;
use App\Models\WarehouseProduct;
use App\Models\WarehouseTranferItems;
use App\Models\WarehouseTransferItems;
use Illuminate\Http\Request;

class Warehouse_TransferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = "Chuyển kho";
        $warehouse = Warehouse::get();
        $products = Products::where('active', 0)->get();
        return view('admin.warehouse_transfer.index', compact('title', 'warehouse', 'products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $title = "Chuyển kho";
        $warehouse = Warehouse::get();
        $products = Products::where('active', 0)->get();
        $data = [
            'date_transfer' => $request->date_transfer,
            'warehouse_from' => $request->warehouse_from,
            'warehouse_to' => $request->warehouse_to,
        ];
        Warehouse_transfer::create($data);
        return view('admin.warehouse_transfer.index', compact('title', 'warehouse', 'products'));
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

    public function warehouseTransferProduct(Request $request)
    {
        $products = $request->product;
        $date = $request->date;
        $warehouse_id_from = $request->warehouse_id_from;
        $warehouse_id_to = $request->warehouse_id_to;
        $quantity = $request->quantity;
        foreach ($products as $product) {
            $from = WarehouseProduct::where('warehouse_id', $warehouse_id_from)->where('product_id', $product)->first();
            $to = WarehouseProduct::where('warehouse_id', $warehouse_id_to)->where('product_id', $product)->first();
            if ($from && $to) {
                $warehouse_transfer = Warehouse_transfer::create([
                    'date_transfer' => $date,
                    'warehouse_from' => $warehouse_id_from,
                    'warehouse_to' => $warehouse_id_to,
                ]);
                if ($from && $from->stock >= $quantity) {
                    if ($from == $to)
                        return response(['message' => 'error'], 403);
                    WarehouseTransferItems::create([
                        'transfer_id' => $warehouse_transfer->id,
                        'product_id' => $from->product_id,
                        'quantity' => $quantity,
                    ]);
                    $from->stock = $from->stock - $quantity;
                    $from->save();
                    $to->stock = $to->stock + $quantity;
                    $to->save();
                    return response(['message' => 'succeed'], 200);
                } else if ($from && !$to) {
                    WarehouseTransferItems::create([
                        'transfer_id' => $warehouse_transfer->id,
                        'product_id' => $from->product_id,
                        'quantity' => $quantity,
                    ]);
                    $from->stock = $from->stock - $quantity;
                    $from->save();
                    $to = WarehouseProduct::create([
                        'warehouse_id' => $warehouse_id_to,
                        'product_id' => $from->product_id,
                        'stock' => $quantity,
                    ]);
                    return response(['message' => 'succeed'], 200);

                } else {
                    return response(['message' => 'quantity error!'], 403);
                }
            } else {
                return response(['message' => 'warehouse from error... or warehouse to error...'], 403);
            }
        }
    }
}
