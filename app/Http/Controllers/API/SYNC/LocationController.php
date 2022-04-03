<?php

namespace App\Http\Controllers\API\SYNC;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BusinessLocation;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $locations = BusinessLocation::all();
            return response()->json($locations, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
