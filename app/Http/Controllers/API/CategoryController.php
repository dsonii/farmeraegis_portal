<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Category;

class CategoryController extends Controller
{
    public function list($parent_id=null)
    {
        try{
            // $business_id = request()->session()->get('user.business_id');
            $business_id = 1;
            $category_type = 'product';
            $categories = Category::where('business_id', $business_id)->where('parent_id', '0')->with('sub_categories');
            $categories = $categories->where('category_type', $category_type);
            $categories = $categories->whereStatus(config('constants.enable'));
            if($parent_id!=null){
                $categories = $categories->where('parent_id', $parent_id);
            }
            $categories = $categories->get();

            return response()->json([
                'response' => [
                    'status'    => 'success',
                    'data'      => $categories,
                    'message'   => 'success'
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'response' => [
                    'status'    => 'failed',
                    'data'      => "",
                    'message'   => $e->getMessage()
                ]
            ], 500);
        }
    }
}
