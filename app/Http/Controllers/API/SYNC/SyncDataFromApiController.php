<?php

namespace App\Http\Controllers\API\SYNC;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Contact;
use App\Address;
use DB;
use App\Utils\TransactionUtil;
use App\Utils\Util;

class SyncDataFromApiController extends Controller
{
    
    protected $commonUtil;
    protected $transactionUtil;
    protected $client;
    
    public function __construct( Util $commonUtil,
        TransactionUtil $transactionUtil)
    {
        $this->client = new \GuzzleHttp\Client();
        $this->commonUtil = $commonUtil;
        $this->transactionUtil = $transactionUtil;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
       $envUrl = env('MOBILE_SERVER_URL').env('MOBILE_SERVER_API_VERSION');
       $url = $envUrl.'customers/list';
       
       $response =  $this->client->request('GET', $url);
       
       $statusCode = $response->getStatusCode();  
       $ids = [];
        if( $statusCode=='200'){
            $content = json_decode($response->getBody(),true);
            if($content){
                $i=0;
                foreach($content as $user){
                    //if($i==0){
                       $ids[] = $user['id'];
                       $phoneCheck = Contact::where('mobile',$user['phone'])->first();
                        if(empty($phoneCheck)){
                            $ref_count = $this->transactionUtil->setAndGetReferenceCount('contacts',1);
                            $contactId = $this->commonUtil->generateReferenceNumber('contacts', $ref_count,1);
                           
                            $contact = Contact::create([
                                'business_id'=>'1',
                                'type'=>'customer',
                                'name'=>$user['f_name'],
                                'first_name'=>$user['f_name'],
                                'last_name'=>$user['l_name'],
                                'email'=>$user['email'],
                                'contact_status'=>'active',
                                'mobile'=>$user['phone'],
                                'contact_id'=>$contactId,
                                'created_by'=>1,
                                'is_synced'=>1
                                ]);
                              
                                if(isset($user['addresses']) and $user['addresses']){
                                    foreach($user['addresses'] as $address){
                                        Address::create([
                                            "address_type"=>"contact",
                                            "user_id"=>$contact->id,
                                            "name"=>$address['contact_person_name'],
                                            "first_line"=>$address['address'],
                                            "second_line"=>$address['full_address'],
                                            "mobile"=>$address['contact_person_number']
                                        ]);
                                    }
                                }
                                
                        }else{
                            $id = $phoneCheck->id;
                            $phoneCheck->address()->delete();
                            $phoneCheck->name = $user['f_name'];
                            $phoneCheck->first_name = $user['f_name'];
                            $phoneCheck->last_name = $user['l_name'];
                            $phoneCheck->email = $user['email'];
                            $phoneCheck->mobile = $user['phone'];
                            $phoneCheck->is_synced = 1;
                            $phoneCheck->save();
                          
                            if(isset($user['addresses']) and $user['addresses'] ){
                                foreach($user['addresses'] as $address){
                                    Address::create([
                                        "address_type"=>"contact",
                                        "user_id"=>$id,
                                        "name"=>$address['contact_person_name'],
                                        "first_line"=>$address['address'],
                                        "second_line"=>$address['full_address'],
                                        "mobile"=>$address['contact_person_number']
                                    ]);
                                }
                            }
                        } 
                    //}
                    $i++;
                }
            }
        }
        if($ids){
            $response1 =  $this->client->request('POST', $envUrl.'customers/synced', ['query' =>['ids'=>$ids]]);
            $content1 = json_decode($response1->getBody(),true);
             dd($content1);
        }
       
    }
    
    public function sanitizePhone($phone){
        $length = strlen($phone);
        if($length!=10){
            $mobile = str_replace("+91","0",$phone);
            if(strlen($mobile)>10){
                    $first_number = substr($mobile, 0, 1); 
                    if ($first_number == 0) {
                      // Check if the first number is 0.
                      // Get rid of the first number.
                      $mobile = substr($mobile, 1, 999); 
                    }
            }
        }
        
        return $mobile;
    }
    
    
    public function getCustomer(Request $request){
        try {
            $users = Contact::with('address')->where('type','customer')->where('is_synced','0')->where('mobile','!=',"")
            ->where('id',"89")
            ->get();
            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
    
    public function syncedCustomers(Request $request){
        $ids = $request->ids;
        if(!empty($ids)){
            Contact::whereIn('id',$ids)->update(['is_synced'=>1]);
        }
        
        return response()->json(['ids'=>$ids], 200);
    }
}