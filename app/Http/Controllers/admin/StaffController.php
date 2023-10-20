<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;


class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function get_debt($id)
    {
        $staff = Staff::find($id);
        return response()->json($staff, 200);
    }
    public function update_debt(Request $request, $id)
    {
        if ($request) {
            $item = Staff::findOrFail($id);
            $item->debt += $request->debt;
            $item->save();
            return response()->json(['update successful', 200]);
        } else {
            return response()->json(['update faild', 401]);
        }
    }

    public function update_status($id)
    {
        $item = Staff::findOrFail($id);
        if ($item) {
            if ($item->active == 0) {
                $item->active += 1;
                $item->save();
                return response()->json(['update successful', 200]);
            } elseif ($item->active == 1) {
                $item->active -= 1;
                $item->save();
                return response()->json(['update successful', 200]);
            }
        } else {
            return response()->json(['update faild', 401]);
        }
    }


    public function index()
    {
        $list_staff = Staff::get();
        $response = [
            'data' => $list_staff,
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
        if ($request->user_id) {
            $request->validate(
                [
                    'fullname' => 'required',
                    'phone' => 'required',
                ],
                [
                    'fullname.required' => 'This field cannot be left blank',
                    'phone.required' => 'This field cannot be left blank',
                ]
            );
            $data = [
                'user_id' => $request->user_id,
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'address' => $request->address,
                'debt' => $request->debt,
            ];
            Staff::create($data);
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
        $item_staff = Staff::findOrFail($id);
        if ($item_staff) {
            $response = [
                'item_staff' => $item_staff,
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
        $item = Staff::findOrFail($id);
        if ($item != null) {
            $data = $request->all();
            $item = Staff::findOrFail($id);
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
        $delete = Staff::findOrFail($id);
        if ($delete) {
            $delete->delete();
            return response()->json(["Delete successful ", 200]);
        } else {
            return response()->json(['message' => 'faild'], 401);
        }
    }
}
