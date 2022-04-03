<?php

namespace App\Http\Controllers\API\SYNC;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Wallet;
use App\User;
use App\Contact;
use App\Address;
use DB;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;

class WalletController extends Controller
{
    protected $commonUtil;
    protected $transactionUtil;
    protected $client;
    
    public function __construct( Util $commonUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->client = new \GuzzleHttp\Client();
        $this->commonUtil = $commonUtil;
        $this->transactionUtil = $transactionUtil;
    }
    
    public function sync(){
        $envUrl = env('MOBILE_SERVER_URL').env('MOBILE_SERVER_API_VERSION');
        $url = $envUrl.'wallets/histories';
        $response =  $this->client->request('GET', $url);
        
        $statusCode = $response->getStatusCode();  
        $ids = [];
        
        $content = json_decode($response->getBody(),true);
        echo '<pre>';
        // print_r($content);
        // exit;
        
        if( $statusCode=='200'){
            $content = json_decode($response->getBody(),true);
            if($content){
                foreach($content as $wallet){
                    $mobile = str_replace("+91", "", $wallet['phone']);
                    $contact = Contact::where('mobile', $wallet['phone'])->first();
                    
                    if(isset($contact) && !empty($contact->id)){
                        
                        $request = array();
                        $ids[] = $wallet['id'];
                        
                        $request['ref_no'] = '';
                        $request['transaction_date'] = date('Y-m-d H:i:s', strtotime($wallet['created_at']));
                        $request['location_id'] = 6;
                        $request['final_total'] = $wallet['amount'];
                        $request['additional_notes'] = $wallet['notes'];
                        $request['expense_category_id'] = '';
                        $request['contact_id'] = $contact->id;
                        
                        // print_r($request);
                        
                        DB::beginTransaction();
            
                            $transaction = $this->transactionUtil->createWalletFromApi($request, 1, 1);
                            
                            $walletss = new Wallet;
                            $walletss->transaction_id = $transaction->id;
                            $walletss->type = $wallet['type'];
                            $walletss->transaction_type = $wallet['transaction_type'];
                            $walletss->is_synced = 1;
                            $walletss->save();

                        DB::commit();
                    }
                }
            }
        }
        
        if($ids){  
            $response1 =  $this->client->request('POST', $envUrl.'wallets/synced', ['query' =>['ids'=>$ids]]);
            $content1 = json_decode($response1->getBody(),true);
        }
        
        print_r($ids);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $wallets = Wallet::select('wallets.*','transactions.final_total','contacts.mobile')
                ->where('wallets.is_synced', 0)
                ->leftJoin('transactions', 'wallets.transaction_id', '=', 'transactions.id')
                ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->get();
                
                if($wallets){
                	foreach($wallets as $wallet){
                		$wall = Wallet::find($wallet->id);
                		$wall->is_synced = 1;
                		$wall->save();
                	}
                }
            return response()->json($wallets, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
