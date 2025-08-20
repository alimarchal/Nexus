<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Minimal stub view to satisfy route listing and autoload
        if (view()->exists('categories.index')) {
            return view('categories.index');
        }

        return response('Category index stub', 200);
    }

    public function create()
    {
        if (view()->exists('categories.create')) {
            return view('categories.create');
        }
        return response('Category create stub', 200);
    }

    public function store(Request $request)
    {
        // Minimal stub: redirect back
        return redirect()->back();
    }

    public function show($id)
    {
        if (view()->exists('categories.show')) {
            return view('categories.show');
        }
        return response('Category show stub', 200);
    }

    public function edit($id)
    {
        if (view()->exists('categories.edit')) {
            return view('categories.edit');
        }
        return response('Category edit stub', 200);
    }

    public function update(Request $request, $id)
    {
        return redirect()->back();
    }

    public function destroy($id)
    {
        return redirect()->back();
    }
}
