<?php

namespace App\Http\Controllers;

use App\Account;

use App\AccountTransaction;
use App\BusinessLocation;
use App\TaxRate;
use App\Transaction;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Contact;
use App\Wallet;

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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('all_wallet.access') && !auth()->user()->can('view_own_wallet')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $wallets = Transaction::leftJoin('wallets', 'transactions.id', '=', 'wallets.transaction_id')
                        ->leftJoin('contacts as U', 'transactions.contact_id', '=', 'U.id')
                        ->leftJoin('users as US', 'transactions.created_by', '=', 'US.id')
                        ->where('transactions.business_id', $business_id)
                        ->whereIn('transactions.type', ['wallet'])
                        ->select(
                            'transactions.id',
                            'transactions.document',
                            'transaction_date',
                            'invoice_no',
                            'ref_no',
                            'wallets.type as wallet_type',
                            'wallets.transaction_type as wallet_transaction_type',
                            'final_total',
                            'category',
                            'additional_notes',
                            DB::raw("CONCAT(COALESCE(U.prefix, ''),' ',COALESCE(U.first_name, ''),' ',COALESCE(U.last_name,''),' ',COALESCE(U.mobile,'')) as wallet_for"),
                            DB::raw("CONCAT(COALESCE(US.surname, ''),' ',COALESCE(US.first_name, ''),' ',COALESCE(US.last_name,''),' ',COALESCE(US.contact_no,'')) as created_by_admin"),
                            'transactions.type'
                        )
                        ->groupBy('transactions.id');

            if (request()->has('customer_id')) {
                $contact_id = request()->get('customer_id');
                if (!empty($contact_id)) {
                    $wallets->where('U.id', $contact_id);
                }
            }

            //Add condition for location,used in sales representative wallet report & list of wallet
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $wallets->where('transactions.location_id', $location_id);
                }
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $wallets->whereIn('transactions.location_id', $permitted_locations);
            }

            $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
            if (!$is_admin && !auth()->user()->can('all_wallet.access')) {
                $user_id = auth()->user()->id;
                $wallets->where(function ($query) use ($user_id) {
                        $query->where('transactions.created_by', $user_id)
                        ->orWhere('contacts.wallet_for', $user_id);
                    });
            }
            
            return Datatables::of($wallets)
                ->addColumn(
                    'action',
                    '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false"> @lang("messages.actions")<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">
                    @if(auth()->user()->can("wallet.edit"))
                        <li><a href="{{action(\'WalletController@edit\', [$id])}}"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a></li>
                    @endif
                    @if($document)
                        <li><a href="{{ url(\'uploads/documents/\' . $document)}}" 
                        download=""><i class="fa fa-download" aria-hidden="true"></i> @lang("purchase.download_document")</a></li>
                        @if(isFileImage($document))
                            <li><a href="#" data-href="{{ url(\'uploads/documents/\' . $document)}}" class="view_uploaded_document"><i class="fas fa-file-image" aria-hidden="true"></i>@lang("lang_v1.view_document")</a></li>
                        @endif
                    @endif
                    @if(auth()->user()->can("wallet.delete"))
                        <li>
                        <a href="#" data-href="{{action(\'WalletController@destroy\', [$id])}}" class="delete_wallet"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a></li>
                    @endif
                    </ul></div>'
                )
                ->removeColumn('id')
                ->editColumn(
                    'final_total',
                    '<span class="display_currency final-total" data-currency_symbol="true" data-orig-value="@if($type=="wallet_refund"){{-1 * $final_total}}@else{{$final_total}}@endif">@if($type=="wallet_refund") - @endif{{$final_total}}</span>'
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn('ref_no', function($row){
                    return $row->ref_no;
                })
                ->editColumn('wallet_transaction_type', function($row){
                    return $row->wallet_transaction_type == 'CR' ? 'CREDIT' : 'DEBIT';
                })
                ->editColumn('wallet_type', function($row){
                    return $row->wallet_type == 'P' ? 'PROMOTIONAL' : 'TRANSACTIONAL';
                })
                ->rawColumns(['category', 'wallet_transaction_type', 'wallet_type', 'final_total', 'action', 'ref_no'])
                ->make(true);
        }

        $business_id = request()->session()->get('user.business_id');

        $users = User::forDropdown($business_id, false, true, true);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        $contacts = Contact::contactDropdown($business_id, false, false);

        return view('wallet.index')
            ->with(compact('business_locations', 'users', 'contacts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('wallet.add')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        
        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('WalletController@index'));
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);

        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $users = User::forDropdown($business_id, true, true);

        $payment_line = $this->dummyPaymentLine;

        $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);

        $contacts = Contact::contactDropdown($business_id, false, false);

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        return view('wallet.create')
            ->with(compact('business_locations', 'users', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'contacts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('wallet.add')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('WalletController@index'));
            }

            $user_id = $request->session()->get('user.id');

            DB::beginTransaction();
            
            $transaction = $this->transactionUtil->createWallet($request, $business_id, $user_id);

            $wallet = new Wallet;
            $wallet->transaction_id = $transaction->id;
            $wallet->type = $request->type;
            $wallet->transaction_type = $request->transaction_type;
            $wallet->category = $request->category;
            $wallet->expired_at = date('Y-m-d H:i:s', strtotime($request->expiry_date));
            $wallet->save();

            $this->transactionUtil->activityLog($transaction, 'added');

            DB::commit();

            $output = ['success' => 1,
                            'msg' => __('wallet.wallet_add_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect('wallets')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('wallet.add')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        
        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('WalletController@index'));
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);

        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $users = User::forDropdown($business_id, true, true);

        $payment_line = $this->dummyPaymentLine;

        $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);

        $contacts = Contact::contactDropdown($business_id, false, false);

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        return view('wallet.edit')
            ->with(compact('business_locations', 'users', 'payment_line', 'payment_types', 'accounts', 'bl_attributes', 'contacts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('wallet.edit')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            //Validate document size
            $request->validate([
                'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
            ]);
            
            $business_id = $request->session()->get('user.business_id');
            
            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('WalletController@index'));
            }

            $wallet = $this->transactionUtil->updateWallet($request, $id, $business_id);

            $this->transactionUtil->activityLog($wallet, 'edited');

            $output = ['success' => 1,
                            'msg' => __('wallet.wallet_update_success')
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect('wallets')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('wallet.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $wallet = Transaction::where('business_id', $business_id)
                                        ->where(function($q) {
                                            $q->where('type', 'wallet')
                                                ->orWhere('type', 'wallet_refund');
                                        })
                                        ->where('id', $id)
                                        ->first();
                $wallet->delete();

                //Delete account transactions
                AccountTransaction::where('transaction_id', $wallet->id)->delete();

                $output = ['success' => true,
                            'msg' => __("wallet.wallet_delete_success")
                            ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }
}
