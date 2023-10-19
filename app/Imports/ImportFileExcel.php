<?php

namespace App\Imports;

use App\Models\ImportFileExecl;
use App\Models\Purchase;
use App\Models\Purchase_item;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportFileExcel implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        dd($row);
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

        // foreach ($request->purchases_item as $item) {
        //     $purchases_item = new Purchase_item([
        //         'purchases_id' => $purchase_id,
        //         'product_id' => $item["product_id"],
        //         'quality' => $item["quality"],
        //         'get_more' => $item["get_more"],
        //         'discount' => $item["discount"],
        //         'price' => $item["price"],
        //     ]);
        //     $purchases_item->save();
        // }
        return new Purchase_item([
            //
        ]);
        
    }
    public function startRow(): int
    {
        return 2;
    }
}
