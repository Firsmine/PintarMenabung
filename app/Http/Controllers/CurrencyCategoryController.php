<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyCategoryController extends Controller
{
    public function currency(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Get all currencies successful',
            'data' => ['currencies' => Currency::get()]
        ]);
    }
    public function category(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Get all currencies successful',
            'data' => ['categories' => Category::get()]
        ]);
    }

}
