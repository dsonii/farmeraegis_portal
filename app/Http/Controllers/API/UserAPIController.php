<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use App\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;
use Prettus\Validator\Exceptions\ValidatorException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use DB;

class UserAPIController extends Controller
{

    function login(Request $request)
    {
        Log::warning($request->input('device_token'));

        if (auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
            // Authentication passed...
            $user = auth()->user();
            $user->device_token = $request->input('device_token');
            $user->save();
            return $this->sendResponse($user, 'User retrieved successfully');
        }

        return $this->sendResponse([
            'error' => 'Unauthenticated user',
            'code' => 401,
        ], 'User not logged');

    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return
     */
    function registration(Request $request)
    {
        try {
            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->contact_number = $request->mobile;
            $user->email = $request->email ?? '';
            $user->language = 'en';
            $user->allow_login = 0;
            $user->password = Hash::make($request->password);
            $user->business_id = 1;
            $user->save();

            $contact = new Contact;
            $contact->business_id = 1;
            $contact->type = 'customer';
            $contact->name = implode(' ', [$request->first_name, $request->last_name]);
            $contact->first_name = $request->first_name;
            $contact->last_name = $request->last_name;
            $contact->mobile = $request->mobile;
            $contact->email = $request->email ?? '';
            $contact->created_by = 1;
            $contact->save();

            $this->send_otp($request->mobile, "$request->otp is your Farmeragies OTP code. Do Not share the OTP with anyone.");
            
            return response()->json([
                'response' => [
                    'status'    => 'success',
                    'data'      => $user,
                    'messege'   => 'Registered successfully. Verify OTP for login.'
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'response' => [
                    'status'    => 'failed',
                    'data'      => '',
                    'messege'   => $e->getMessage()
                ]
            ], 500);
        }
    }

    function logout(Request $request)
    {

        try {

            auth()->logout();
            
            return response()->json([
                'response' => [
                    'status'    => 'success',
                    'data'      => '',
                    'messege'   => 'Logout successfully'
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'response' => [
                    'status'    => 'failed',
                    'data'      => '',
                    'messege'   => $e->getMessage()
                ]
            ], 500);
        }

    }

    public function send_otp($number, $content)
    {
        try{

            $otp = mt_rand(1000, 9999);
            $client = new Client();
            $mobile = $number;

            $message = str_replace("&", " and ", $content);
            $message = preg_replace('~[\r\n]+~', '%0a', $message);
            $message = urlencode($message);
            
            $response = $client->request('GET', "http://sms4power.com/api/swsendSingle.asp?username=t1FARMERAEGIS&password=10385682&sender=FARMIR&sendto=91$mobile&entityID=1701161548065947136&templateID=1707161804011056066&message=$message");
 
            $json = $response->getBody()->getContents();

        } catch (\Exception $e) {

        }
    }

    public function user_login(Request $request)
    {
        try{    
            
            if(isset($request->password) && !empty($request->password)){

                if(is_numeric($request->username)){
                    auth()->attempt(['contact_no' => $request->username, 'password' => $request->password]);
                    $user = auth()->user();
                }
                
                if(filter_var($request->username, FILTER_VALIDATE_EMAIL)){
                    if (auth()->attempt(['email' => $request->username, 'password' => $request->password])) {
                        $user = auth()->user();
                    }
                }
            }
            
            if(isset($user)){
                $user->remember_token = Str::random(100);
                $user->save();
                $access_token_example = $user->createToken('apiuser.in');
                return response()->json([
                    'response' => [
                        'status'    => 'success',
                        'data'      => [
                            'user' => $user,
                            'token' => $access_token_example
                        ],
                        'messege'   => 'success',
                    ]
                ], 200);
            }

            return response()->json([
                'response' => [
                    'status'    => 'failed',
                    'data'      => [],
                    'messege'   => 'Wrong Credintials',
                ]
            ], 401);
            
        } catch (\Exception $e) {

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            return response()->json([
                'response' => [
                    'status'    => 'failed',
                    'data'      => '',
                    'messege'   => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function update_profile(Request $request)
    {
        try{          

            $user = User::find(Auth::id());
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->contact_number = $request->mobile;
            if(isset($request->password)){
                $user->password = Hash::make($request->password);
            }
            $user->save();

            return response()->json([
                'response' => [
                    'status'    => 'success',
                    'data'      => $user,
                    'messege'   => 'Profile Updated successfully'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'response' => [
                    'status'    => 'failed',
                    'data'      => '',
                    'messege'   => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function get_profile()
    {
        try{          

            $user = User::find(Auth::id());

            return response()->json([
                'response' => [
                    'status'    => 'success',
                    'data'      => $user,
                    'messege'   => 'success'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'response' => [
                    'status'    => 'failed',
                    'data'      => '',
                    'messege'   => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function orders()
    {
        try{

            $data['orders'] = Order::where('user_id', Auth::id())->with('payment')->get();
            $data['status'] = OrderStatus::select('id','status')->get();

            return response()->json([
                'response' => [
                    'status'    => 'success',
                    'data'      => $data,
                    'messege'   => 'Order List'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'response' => [
                    'status'    => 'failed',
                    'data'      => '',
                    'messege'   => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function order_details($id)
    {
        try{

            $data['orders'] = Order::where('id', $id)->with('payment')->first();

            $data['address'] = Address::where('id', $data['orders']->address_id)->first();

            $data['products'] = FoodOrder::select('foods.*','media.id as file_folder','media.file_name','store_products.quantity_type','food_orders.quantity','food_orders.price')
                        ->join('foods', 'food_orders.food_id', '=', 'foods.id')
                        ->join('media', 'foods.id', '=', 'media.model_id')
                        ->join('store_products', 'store_products.food_id', '=', 'foods.id')
                        ->where('food_orders.order_id', $id)
                        ->where('media.model_type', 'App\Models\Food')
                        ->get();

            return response()->json([
                'response' => [
                    'status'    => 'success',
                    'data'      => $data,
                    'messege'   => 'Order List'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'response' => [
                    'status'    => 'failed',
                    'data'      => '',
                    'messege'   => $e->getMessage()
                ]
            ], 500);
        }
    }
}
