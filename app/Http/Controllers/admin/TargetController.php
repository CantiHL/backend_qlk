<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Product_Group;
use App\Models\Staff;
use App\Models\Target;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $data = Target::when($from_date, function (Builder $query, $from_date) {
            $formatted_date = Carbon::createFromFormat('d-m-Y', $from_date)->format('Y-m-d');
            $query->whereDate('from_date', '>=', $formatted_date);
        })
            ->when($to_date, function (Builder $query, $to_date) {
                $formatted_date = Carbon::createFromFormat('d-m-Y', $to_date)->format('Y-m-d');
                $query->whereDate('to_date', '<=', $formatted_date);
            })
            ->select('id', 'staff_id', 'group_product_id', 'target', 'from_date', 'to_date')->orderBy('id', 'desc')->get();
        $res = [
            'data' => $data,
        ];
        return response()->json($res, 200);
    }
    public function create(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|integer',
            'group_product_id' => 'required|integer',
            'target' => 'required|integer',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);
        Target::create([
            'staff_id' => $request->staff_id,
            'group_product_id' => $request->group_product_id,
            'target' => $request->target,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
        ]);
        return response()->json(['message' => 'succeed'], 201);
    }
}
