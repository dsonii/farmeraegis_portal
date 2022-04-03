<?php

namespace App\Http\Controllers\API\SYNC;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $prodArr = array();
            $products = DB::table('variations')
                ->leftJoin('product_locations', 'variations.product_id', '=', 'product_locations.product_id')
                ->leftJoin('products', 'products.id', '=', 'product_locations.product_id')
                ->select('variations.id as variations_id', 'variations.name as variations_name', 'variations.sell_price_inc_tax', 'variations.mrp', 'products.*', 'product_locations.location_id')
                ->where('products.id', '!=', NULL)
                ->where('products.not_for_selling', '==', 0)
                ->get();

                if($products){
                    foreach($products as $key => $product){
                        $medias = DB::table('media')->where('model_id', $product->variations_id)->get();
                        $products[$key]->media = $medias;
                    }
                }

            return response()->json($products, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
