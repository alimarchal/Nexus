<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function product()
    {
        return view('product.index');

    }
    public function branchSetting()
    {
        return view('product.daily-positions'); 
    }

}
