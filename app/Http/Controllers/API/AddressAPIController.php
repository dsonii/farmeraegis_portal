<?php

namespace App\Http\Controllers\API;

use App\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use DB;

/**
 * Class AddressController
 * @package App\Http\Controllers\API
 */

class AddressAPIController extends Controller
{
    /**
     * Display a listing of the Address.
     * GET|HEAD /addresses
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try{

            $addresses = Address::where('user_id', Auth::id())->get();

            if($addresses){
                return response()->json([
                    'response' => [
                        'status'    => 'success',
                        'data'      => $addresses,
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
     * Store a newly created Address in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try{

            DB::table('addresses')->insert([
                'user_id'       => Auth::id(),
                'name'          => $request->name,
                'first_line'    => $request->first_line,
                'second_line'   => $request->second_line,
                'mobile'        => $request->mobile,
                'alt_mobile'    => $request->alt_mobile,
                'city'          => $request->city,
                'state'         => $request->state,
                'country'       => $request->country,
                'pincode'       => $request->pincode,
                'near_by'       => $request->near_by,
            ]);

            $addresses = Address::where('user_id', Auth::id())->get();

            if($addresses){
                return response()->json([
                    'response' => [
                        'status'    => 'success',
                        'data'      => $addresses,
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
     * Update the specified Address in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        try{

            DB::table('addresses')
            ->where('id', $id)
            ->update([
                'user_id'       => Auth::id(),
                'name'          => $request->name,
                'first_line'    => $request->first_line,
                'second_line'   => $request->second_line,
                'mobile'        => $request->mobile,
                'alt_mobile'    => $request->alt_mobile,
                'city'          => $request->city,
                'state'         => $request->state,
                'country'       => $request->country,
                'pincode'       => $request->pincode,
                'near_by'       => $request->near_by,
            ]);

            $addresses = Address::where('user_id', Auth::id())->get();

            if($addresses){
                return response()->json([
                    'response' => [
                        'status'    => 'success',
                        'data'      => $addresses,
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
     * Remove the specified Favorite from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try{

            DB::table('addresses')->where('id', '=', $id)->delete();

            $addresses = Address::where('user_id', Auth::id())->get();

            if($addresses){
                return response()->json([
                    'response' => [
                        'status'    => 'success',
                        'data'      => $addresses,
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
