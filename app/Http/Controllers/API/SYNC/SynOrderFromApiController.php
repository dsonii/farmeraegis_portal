<?php

namespace App\Http\Controllers\API\SYNC;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Contact;
use App\Address;
use App\Product;
use App\Transaction;
use DB;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use App\BusinessLocation;
use \App\TransactionPayment;
use App\TransactionSellLine;

class SynOrderFromApiController extends Controller
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
       $url = $envUrl.'orders/list';
       $response =  $this->client->request('GET', $url);
       
       $statusCode = $response->getStatusCode();  
       $ids = [];
        if( $statusCode=='200'){
            $content = json_decode($response->getBody(),true);
            
            if($content){
                $i=0;
                
                foreach($content as $orders){
                    //if($orders['id']==100093){
                    $getProduct= $branchId ="";
                    $getBranch = BusinessLocation::where('id',$orders['branch']['location_id'])->first();
                    if($getBranch){
                        $branchId = $getBranch->id;
                    }
                    
                    $discountType = 'amount';
                    if($orders['details']){
                        foreach($orders['details'] as $details){
                             if(empty($details['product'])){
                                continue;
                            }
                            $locationId = $details['product']['location_id'];
                           
                            $variationId = $details['product']['variations_id'];
                            
                            $checkForProduct = Product::with(['product_locations','variations'])
                            ->whereHas('product_locations',function($query) use ($locationId){
                                $query->where('id',$locationId);
                            })
                            ->whereHas('variations',function($query) use ($variationId){
                                $query->where('id',$variationId);
                            })
                            ->where('id',$details['product']['products_id'])->first();
                            if(empty($checkForProduct)){
                                continue;
                            }
                            $discountType = $checkForProduct->discount_type;
                        }
                    }
                  
                    if(!isset($orders['customer']['phone']) || empty($orders['customer']['phone'])){
                         continue;
                    }
                     
                    $getCustomer = Contact::where('mobile',$orders['customer']['phone'])->first();
                     // dd($branchId,$getCustomer);
                    if(empty($branchId) || empty($getCustomer)){
                        continue;
                    }
                   
                    $transaction = "";   
                  if(!empty($orders['primeryId'])){
                    $transaction = Transaction::where('type',"sell")->
                        orWhereIn('sales_order_ids',[$orders['primeryId']])
                        ->orderBy('id','desc')->first();
                  }
                     
                    if(empty($transaction)){
                        $transaction = Transaction::orWhere('invoice_no',$orders['id']);
                        if(!empty($orders['ref_no'])){
                            $transaction = $transaction->orWhere('invoice_no',$orders['ref_no']);
                        }
                        $transaction = $transaction->orderBy('id','desc')->first();
                    }
                    
                    if(empty($transaction)){
                        $transaction = new Transaction();
                        $transaction->type = "sales_order";
                        $transaction->status = "ordered";
                        $transaction->invoice_no = $orders['id'];
                         $transaction->is_online = 1;
                    }
                   
                    
                        $paymentStatus = ['unpaid'=>'due','paid'=>'paid'];
                        $transaction->business_id = 1;
                        $transaction->location_id = $branchId;
                        $transaction->payment_status = $paymentStatus[$orders['payment_status']]??'';
                        $transaction->contact_id = $getCustomer->id;
                        if(!empty($orders['date'])){
                            $transaction->transaction_date = date('Y-m-d H:i:s',strtotime($orders['date']));
                        }
                        
                        
                        $transaction->tax_amount = $orders['total_tax_amount'];
                        if($discountType=='amount'){
                            $transaction->discount_type = "fixed";
                        }else{
                            $transaction->discount_type = "percentage";
                        }
                        
                        
                      
                       if($orders['delivery_address']){
                           $address = $orders['delivery_address']['address'].' '.$orders['delivery_address']['full_address'];
                            
                           $transaction->shipping_address = $address;
                           
                           $transaction->delivered_to = $orders['delivery_address']['contact_person_name'];
                       }
                        
                       $orderStatus = ["pending"=>"ordered","confirmed"=>"ordered","processing"=>"packed","out_for_delivery"=>"shipped","delivered"=>"delivered","canceled"=>"cancelled"
                       ,"returned"=>"returned","failed"=>"failed"];
                       
                        $transaction->shipping_status = $orderStatus[$orders['order_status']];
                        
                        $transaction->shipping_charges = $orders['delivery_charge'];
                       
                        $transaction->export_custom_fields_info;
                        
                        $transaction->created_by =1;
                       
                        $paymentMethod = ["razor_pay"=>"custom_pay_4","cash_on_delivery"=>"cash"];
                        
                       $tp = ["T"=>"custom_pay_2","P"=>"custom_pay_3"];
                       $orderAmount = $orders['order_amount'];
                       if(empty($orders['wallet'])){
                           if(!empty($orders['payment_method'])){
                                $transaction->prefer_payment_method  = $paymentMethod[$orders['payment_method']];
                           }
                       }else{
                           if(!empty($orders['wallet'])){
                               $type = $orders['wallet']['type'];
                               $transaction->prefer_payment_method  = $tp[$type];
                               if($orders['wallet']['transaction_type'] =="DR" ){
                                   if(empty($orders['order_amount'])){
                                        $orderAmount= $orders['wallet']['amount'];
                                         $transaction->payment_status = 'paid';
                                   }
                               }
                           }
                       }
                       $discountAmount = 0;
                        if(!empty($orders['coupon_discount_amount'])){
                            if($discountType=='amount'){
                                $discountAmount = $orders['coupon_discount_amount'];
                            }else{
                                $discountAmount = 100/(($orderAmount+$orders['coupon_discount_amount'])/$orders['coupon_discount_amount']);
                            }
                            
                        }
                       
                        $transaction->discount_amount = $discountAmount;
                        
                        if(!empty($discountAmount)){
                            $transaction->round_off_amount = -($orders['coupon_discount_amount']);
                        }else{
                            $transaction->round_off_amount = $discountAmount;
                        }
                        
                        $transaction->is_direct_sale = 1;
                        $transaction->final_total = $orderAmount;
                        $transaction->total_before_tax = $orderAmount+$orders['coupon_discount_amount'];
                        $serviceTye = ["delivery"=>"2","self_pickup"=>'3'];
                        $transaction->types_of_service_id = $serviceTye[$orders['order_type']];
                        $transaction->is_synced=1;
                        $transactionData = $transaction->save();
                        
//dd($orders);
                       // Add Payment    
                        if(!empty($orders['payment_method']) and empty($orders['wallet'])){
                            if($paymentMethod[$orders['payment_method']]!='cash'){
                               
                                $transactionPayment = new TransactionPayment();
                                $transactionPayment->business_id=1;
                                $transactionPayment->amount=$orders['order_amount'];
                                $transactionPayment->method= "custom_pay_4";
                                $transactionPayment->transaction_no= $orders['transaction_reference'];
                                $transactionPayment->card_type= 'credit';
                                $links = array(
                                    $transactionPayment
                                );
                                
                                $transaction->payment_lines()->delete();
                                $transaction->payment_lines()->saveMany($links);
                            }else{
                                if($orders['payment_method']=='cash_on_delivery' and $orders['payment_status']=='paid' and $orders['order_status']=='delivered'){
                                     $transactionPayment = new TransactionPayment();
                                    $transactionPayment->business_id=1;
                                    $transactionPayment->amount=$orders['order_amount'];
                                    $transactionPayment->method= "cash";
                                    $transactionPayment->transaction_no= $orders['transaction_reference'];
                                    $transactionPayment->card_type= 'credit';
                                    $links = array(
                                        $transactionPayment
                                    );
                                    
                                    $transaction->payment_lines()->delete();
                                    $transaction->payment_lines()->saveMany($links);
                                }
                            }
                        }else{
                          
                           $links = [];
                           if(!empty($orders['wallet'])){
                                if(!empty($orders['order_amount']) and $orders['payment_status']=='paid'){
                                     if(!empty($orders['details'])){
                                         $amountTotal = 0;
                                         
                                         foreach($orders['details'] as $priceCalc){
                                             $amountTotal+=$priceCalc['price'];
                                         }
                                     }
                                     //dd(!empty($orders['order_payments']),$amountTotal,$orders['order_amount'],$orders['order_payments']['amount'], $amountTotal==($orders['order_amount']+$orders['order_payments']['amount']) , $orders['order_payments']['method']=='promotional_wallet');
                                     if(!empty($orders['order_payments']) and $amountTotal==($orders['order_amount']+$orders['order_payments']['amount']) and $orders['order_payments']['method']=='promotional_wallet'){
                                        $transactionPayment =  new TransactionPayment();
                                        $transactionPayment->business_id=1;
                                        $transactionPayment->amount=$orders['order_amount'];
                                        $transactionPayment->method= "custom_pay_4";
                                        $transactionPayment->transaction_no= $orders['transaction_reference'];
                                        $transactionPayment->card_type= 'credit';
                                        $links[] = $transactionPayment;
                                     }
                                }
                                if($orders['wallet']['transaction_type']=='DR'){
                                    $type = $orders['wallet']['type'];
                                    $transactionPayment1 =  new TransactionPayment();
                                    $transactionPayment1->business_id=1;
                                    if(!empty($orders['order_payments'])){
                                        $transactionPayment1->amount=$orders['order_payments']['amount'];
                                    }else{
                                        $transactionPayment1->amount=$orders['wallet']['amount'];
                                    }
                                    $transactionPayment1->method= $tp[$type];
                                    $transactionPayment1->transaction_no= $orders['wallet']['transaction_id'];
                                    $transactionPayment1->card_type= 'credit';
                                    $links[] = $transactionPayment1;
                                } 
                                if(!empty($links)){
                                    $transaction->payment_lines()->delete();
                                    $transaction->payment_lines()->saveMany($links);
                                }
                           }
                        }
                    $saveProduct = [];
                    if(!empty($orders['details']) && $orders['details']){
                        foreach($orders['details'] as $details){
                            $transactionSellLine = new TransactionSellLine();
                            if(empty($details['product']['products_id'])){
                               continue;
                            }
                            $transactionSellLine->product_id = $details['product']['products_id'];
                            $transactionSellLine->variation_id = $details['product']['variations_id'];
                            $transactionSellLine->quantity=$details['quantity'];
                            $dType = 'fixed';
                            if($details['discount_type']=='discount_on_product'){
                                $dType = "percent";
                            }
                            $transactionSellLine->unit_price=$details['price'];
                            $transactionSellLine->unit_price_before_discount=$details['price'];
                            $transactionSellLine->line_discount_type=$dType;
                            $transactionSellLine->line_discount_amount=$details['discount_on_product'];
                            $transactionSellLine->unit_price_inc_tax=$details['tax_amount']+$details['price'];
                            $transactionSellLine->item_tax = $details['tax_amount'];
                            $saveProduct[] = $transactionSellLine;
                        }
                    }
                   
                    if(!empty($saveProduct)){
                        $transaction->sell_lines()->delete();
                        $transaction->sell_lines()->saveMany($saveProduct);
                    }
                    //TransactionSellLine
                    $ids[] =$orders['id'];
                        $i++;
                   // }
                }
                if($ids){  
                    $response1 =  $this->client->request('POST', $envUrl.'orders/synced', ['query' =>['ids'=>$ids]]);
                    $content1 = json_decode($response1->getBody(),true);
                     dd($content1);
                }
            }
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
    
    
    public function getOrders(Request $request){
        try {
            $orders = Transaction::with(['purchase_lines'
            ,'sell_lines','contact','payment_lines','location','business','tax','stock_adjustment_lines'
            ,'sales_person','service_staff','types_of_service'])->where('is_synced','0')
            ->whereRaw(DB::raw('(type="sell" or type="sales_order")'))
            ->whereDate('created_at','>=','2021-12-11')
            ->orderBy('id','asc')
            ->get();
            if($orders->isNotEmpty()){
                foreach($orders as $key=>$order){
                    if(!empty($order->sales_order_ids)){
                        $arr = $order->sales_order_ids;
                        $invoiceData = Transaction::select('id','invoice_no')->whereIn('id',$arr)->get();
                        $refNumbers = $invoiceIds = [];
                        if($invoiceData->isNotEmpty()){
                            
                            $refNumbers = $invoiceData->pluck('id')->toArray();
                            $invoiceIds = $invoiceData->pluck('invoice_no')->toArray();
                        }
                        $orders[$key]->setAttribute('refNumbers', $refNumbers);
                        $orders[$key]->setAttribute('invoiceIds', $invoiceIds);
                    }
                }
            }
            return response()->json($orders, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
    
    public function syncedOrders(Request $request){
        $ids = $request->ids;
        if(!empty($ids)){
            Transaction::whereIn('id',$ids)->update(['is_synced'=>1]);
        }
        
        return response()->json(['ids'=>$ids], 200);
    }
}