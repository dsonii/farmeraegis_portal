<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Product;

class ProductController extends Controller
{
    private $perPage = 8;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list($business_id, Request $request)
    {
        $filters = $request->filter;
        $search = [];
        $pagination = true;
        try{
            $query = Product::select('products.*', 'variations.name as variation_name', 'variations.id as variation_id', 'variations.default_sell_price', 'variations.mrp')->where('business_id', $business_id)->leftJoin('variations', 'products.id', 'variations.product_id');

            $with = ['product_variations.variations.variation_location_details', 'brand', 'unit', 'category', 'sub_category', 'product_tax', 'product_variations.variations.media', 'product_locations'];

            if (!empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (!empty($filters['sub_category_id'])) {
                $query->where('sub_category_id', $filters['sub_category_id']);
            }

            if (!empty($filters['brand_id'])) {
                $query->where('brand_id', $filters['brand_id']);
            }

            if (!empty($filters['selling_price_group']) && $filters['selling_price_group'] == true) {
                $with[] = 'product_variations.variations.group_prices';
            }
            if (!empty($filters['location_id'])) {
                $location_id = $filters['location_id'];
                $query->whereHas('product_locations', function($q) use($location_id) {
                    $q->where('product_locations.location_id', $location_id);
                });
            }

            if (!empty($filters['product_ids'])) {
                $query->whereIn('id', $filters['product_ids']);
            }

            $query->where('not_for_selling', 0);
            $query->where('is_inactive', 0);            

            $perPage = !empty($filters['per_page']) ? $filters['per_page'] : $this->perPage;

            if (!empty($search)) {
                $query->where(function ($query) use ($search) {

                    if (!empty($search['name'])) {
                        $query->where('products.name', 'like', '%' . $search['name'] .'%');
                    }
                    
                    if (!empty($search['sku'])) {
                        $sku = $search['sku'];
                        $query->orWhere('sku', 'like', '%' . $sku .'%');
                        $query->orWhereHas('variations', function($q) use($sku) {
                            $q->where('variations.sub_sku', 'like', '%' . $sku .'%');
                        });
                    }
                });
            }

            // $query->where('id', $filters['product_ids']);
            $query->groupBy('variations.id');

            $query->with($with);

            if ($pagination && $perPage != -1) {
                $products = $query->paginate($perPage);
                $products->appends(request()->query());
            } else{
                $products = $query->get();
            }

            return response()->json([
                'response' => [
                    'status'    => 'success',
                    'data'      => $products,
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

    public function details($id, $variation_id)
    {
        try{
            $query = Product::where('products.id', $id)
                ->select('products.*', 'variations.name as variations_name',  'variations.id as variation_id', 'variations.default_sell_price', 'variations.mrp')
                ->join('variations', 'products.id', '=', 'variations.product_id')
                ->where('variations.id', $variation_id);

            $with = ['brand', 'category', 'sub_category', 'product_variations.variations.media'];

            $query->with($with);

            $products = $query->first();

            return response()->json([
                'response' => [
                    'status'    => 'success',
                    'data'      => $products,
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
