<?php

namespace App\Http\Controllers\API;

use App\Promocode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PromocodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{

            $promocodes = Promocode::whereStatus('A')->get();

            if($promocodes){
                return response()->json([
                    'response' => [
                        'status'    => 'success',
                        'data'      => $promocodes,
                        'message'   => 'success'
                    ]
                ], 200);
            } else {
                return response()->json([
                    'response' => [
                        'status'    => 'failed',
                        'data'      => "",
                        'message'   => $e->getMessage()
                    ]
                ], 500);
            }
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function verify($promocode)
    {
        try{

            $promocodes = Promocode::wherePromocode($promocode)->whereStatus('A')->get();

            if($promocodes){
                return response()->json([
                    'response' => [
                        'status'    => 'success',
                        'data'      => $promocodes,
                        'message'   => 'success'
                    ]
                ], 200);
            } else {
                return response()->json([
                    'response' => [
                        'status'    => 'failed',
                        'data'      => "",
                        'message'   => $e->getMessage()
                    ]
                ], 500);
            }
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
