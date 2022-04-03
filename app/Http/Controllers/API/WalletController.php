<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use App\User;
use App\Transaction;
use DB;

class WalletController extends Controller
{
    /**
    * Constructor
    *
    * @param TransactionUtil $transactionUtil
    * @return void
    */
    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
        'is_return' => 0, 'transaction_no' => ''];
    }

    public function transactional()
    {
        try {
            $contactId = 2;
            $credit = Transaction::whereContactId($contactId)
                ->where('transactions.type', 'wallet')
                ->leftJoin('wallets', 'transactions.id', '=', 'wallets.transaction_id')
                ->where('wallets.transaction_type', 'CR')
                ->where('wallets.type', 'T')
                ->sum('final_total');
            
            $dabit = Transaction::whereContactId($contactId)
                ->where('transactions.type', 'wallet')
                ->leftJoin('wallets', 'transactions.id', '=', 'wallets.transaction_id')
                ->where('wallets.transaction_type', 'DR')
                ->where('wallets.type', 'T')
                ->sum('final_total');
            
            return response()->json([
                'response' => [
                    'status'    => 'success',
                    'data'      => ((float) $credit - (float) $dabit),
                    'messege'   => 'Success'
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

    public function promotional()
    {
        try {
            $contactId = 2;
            $credit = Transaction::whereContactId($contactId)
                ->where('transactions.type', 'wallet')
                ->leftJoin('wallets', 'transactions.id', '=', 'wallets.transaction_id')
                ->where('wallets.transaction_type', 'CR')
                ->where('wallets.type', 'P')
                ->sum('final_total');
            
            $dabit = Transaction::whereContactId($contactId)
                ->where('transactions.type', 'wallet')
                ->leftJoin('wallets', 'transactions.id', '=', 'wallets.transaction_id')
                ->where('wallets.transaction_type', 'DR')
                ->where('wallets.type', 'P')
                ->sum('final_total');
            
            return response()->json([
                'response' => [
                    'status'    => 'success',
                    'data'      => ((float) $credit - (float) $dabit),
                    'messege'   => 'Success'
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $contacts = User::select('contacts.id')->whereId(Auth::id())->join('contacts', 'users.contact_no', '=', 'contacts.mobile')->first();

            $request->location_id = 1;
            $request->expiry_date = NULL;
            $request->type = 'T';
            $request->transaction_type = 'CR';
            $request->contact_id = $contacts->id;

            $business_id = 1;

            $user_id = Auth::id();

            DB::beginTransaction();
            
            $transaction = $this->transactionUtil->createWallet($request, $business_id, $user_id);

            $wallet = new Wallet;
            $wallet->transaction_id = $transaction->id;
            $wallet->type = $request->type;
            $wallet->transaction_type = $request->transaction_type;
            $wallet->expired_at = date('Y-m-d H:i:s', strtotime($request->expiry_date));
            $wallet->save();

            $this->transactionUtil->activityLog($transaction, 'added');

            DB::commit();

            return response()->json([
                'response' => [
                    'status'    => 'success',
                    'data'      => $wallet,
                    'messege'   => 'Wallet added successfully'
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
