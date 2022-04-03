<?php

namespace App\Http\Controllers\API\SYNC;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $users = User::All();

            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
