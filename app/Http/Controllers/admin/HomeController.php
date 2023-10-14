<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function Dashboard()
    {
        $title = "Quản lý kho";
        return view('admin.home.index', compact('title'));
    }
}