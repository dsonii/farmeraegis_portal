<?php

namespace App\Http\Controllers;
use App\QuantityFinder;
use Datatables;

use DB;
use Illuminate\Http\Request;

class WarehouseQuantityController extends Controller
{
    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('product.quantity_finder_list')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
           
            $stock_transfers = QuantityFinder::
            select(
                'id',
                'refrence_number',
                'created_at',
                'updated_at'
            );
            
            return Datatables::of($stock_transfers)
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->rawColumns(['refrence_number', 'created_at', 'id'])
                ->setRowAttr([
                'data-href' => function ($row) {
                    return  action('WarehouseQuantityController@show', [$row->id]);
                }])
                ->make(true);
        }
        return view('warehouse_quantity.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('product.quantity_finder_list')) {
            abort(403, 'Unauthorized action.');
        }
        $stock_transfers = QuantityFinder::with(['quantiy', 'quantiy.product'])
                            ->select(
                                'id',
                                'refrence_number',
                                'created_at',
                                'updated_at'
                            )
                            ->where('id', $id)
                            ->first();
        
        return view('warehouse_quantity.show')
                ->with(compact('stock_transfers'));
    }

    public function warehouseQty(Request $request) {
        if (!auth()->user()->can('product.quantity_finder_list')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $id = $request->id;
            $warehouseQty = $request->warehouseQty;
            $remaningQty = $request->remaningQty;
            $sellLine = QuantityFinder::find($id);
            if ($sellLine->demand_quantity < $warehouseQty) {
                return json_encode(false);
            }
            $sellLine->ware_house_quantity = $warehouseQty;
            $sellLine->purchase_quantity = $remaningQty;
            $sellLine->save();
            return json_encode(true);
        }
    }

    
}
