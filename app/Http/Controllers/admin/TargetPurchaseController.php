<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\TargetPurchase;
use Illuminate\Http\Request;

class TargetPurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = TargetPurchase::select('id', 'date', 'target')->orderBy('id', 'desc')->get();
        $res = [
            'data' => $data,
        ];
        return response()->json($res);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'target' => 'required|integer',
        ]);
        $targetPurchase = TargetPurchase::create([
            'date' => $request->date,
            'target' => $request->target,
        ]);
        if (!$targetPurchase) {
            return response()->json(['message' => 'error'], 201);
        }
        return response()->json(['message' => 'succeed'], 201);
    }
}
