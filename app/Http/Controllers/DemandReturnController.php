<?php

namespace App\Http\Controllers;

use App\AccountTransaction;
use App\DemandLine;
use App\Transaction;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\BusinessLocation;
use Spatie\Activitylog\Models\Activity;

class DemandReturnController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $transactionUtil;
    protected $productUtil;

    /**
     * Constructor
     *
     * @param TransactionUtil $transactionUtil
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('demand.view') && !auth()->user()->can('demand.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $demands_returns = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->join(
                        'business_locations AS BS',
                        'transactions.location_id',
                        '=',
                        'BS.id'
                    )
                    ->leftJoin(
                        'transactions AS T',
                        'transactions.return_parent_id',
                        '=',
                        'T.id'
                    )
                    ->leftJoin(
                        'transaction_payments AS TP',
                        'transactions.id',
                        '=',
                        'TP.transaction_id'
                    )
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'demand_return')
                    ->select(
                        'transactions.id',
                        'transactions.transaction_date',
                        'transactions.ref_no',
                        'contacts.name',
                        'transactions.status',
                        'transactions.payment_status',
                        'transactions.final_total',
                        'transactions.return_parent_id',
                        'BS.name as location_name',
                        'T.ref_no as parent_demand',
                        DB::raw('SUM(TP.amount) as amount_paid')
                    )
                    ->groupBy('transactions.id');

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $demands_returns->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->location_id)) {
                $demands_returns->where('transactions.location_id', request()->location_id);
            }
            
            if (!empty(request()->supplier_id)) {
                $supplier_id = request()->supplier_id;
                $demands_returns->where('contacts.id', $supplier_id);
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $demands_returns->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }
            return Datatables::of($demands_returns)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                        data-toggle="dropdown" aria-expanded="false">' .
                                        __("messages.actions") .
                                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" role="menu">';
                    if (!empty($row->return_parent_id)) {
                        $html .= '<li><a href="' . action('DemandReturnController@add', $row->return_parent_id) . '" ><i class="glyphicon glyphicon-edit"></i>' .
                                __("messages.edit") .
                                '</a></li>';
                    } else {
                        $html .= '<li><a href="' . action('CombinedDemandReturnController@edit', $row->id) . '" ><i class="glyphicon glyphicon-edit"></i>' .
                                __("messages.edit") .
                                '</a></li>';
                    }

                    if ($row->payment_status != "paid") {
                        $html .= '<li><a href="' . action('TransactionPaymentController@addPayment', [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i>' . __("demand.add_payment") . '</a></li>';
                    }

                    $html .= '<li><a href="' . action('TransactionPaymentController@show', [$row->id]) . '" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i>' . __("demand.view_payments") . '</a></li>';

                    $html .= '<li><a href="' . action('DemandReturnController@destroy', $row->id) . '" class="delete_demand_return" ><i class="fa fa-trash"></i>' .
                                __("messages.delete") .
                                '</a></li>';
                    $html .= '</ul></div>';
                    
                    return $html;
                })
                ->removeColumn('id')
                ->removeColumn('return_parent_id')
                ->editColumn(
                    'final_total',
                    '<span class="display_currency final_total" data-currency_symbol="true" data-orig-value="{{$final_total}}">{{$final_total}}</span>'
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'payment_status',
                    '<a href="{{ action("TransactionPaymentController@show", [$id])}}" class="view_payment_modal payment-status payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="@if($payment_status != "paid"){{__(\'lang_v1.\' . $payment_status)}}@else{{__("lang_v1.received")}}@endif"><span class="label @payment_status($payment_status)">@if($payment_status != "paid"){{__(\'lang_v1.\' . $payment_status)}} @else {{__("lang_v1.received")}} @endif
                        </span></a>'
                )
                ->editColumn('parent_demand', function ($row) {
                    $html = '';
                    if (!empty($row->parent_demand)) {
                        $html = '<a href="#" data-href="' . action('DemandController@show', [$row->return_parent_id]) . '" class="btn-modal" data-container=".view_modal">' . $row->parent_demand . '</a>';
                    }
                    return $html;
                })
                ->addColumn('payment_due', function ($row) {
                    $due = $row->final_total - $row->amount_paid;
                    return '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $due . '">' . $due . '</sapn>';
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("demand.view")) {
                            $return_id = !empty($row->return_parent_id) ? $row->return_parent_id : $row->id;
                            return  action('DemandReturnController@show', [$return_id]) ;
                        } else {
                            return '';
                        }
                    }])
                ->rawColumns(['final_total', 'action', 'payment_status', 'parent_demand', 'payment_due'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('demand_return.index')->with(compact('business_locations'));
    }

    /**
     * Show the form for demand return.
     *
     * @return \Illuminate\Http\Response
     */
    public function add($id)
    {
        if (!auth()->user()->can('demand.update')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        $demand = Transaction::where('business_id', $business_id)
                        ->where('type', 'demand')
                        ->with(['demand_lines', 'contact', 'tax', 'return_parent', 'demand_lines.sub_unit', 'demand_lines.product', 'demand_lines.product.unit'])
                        ->find($id);

        foreach ($demand->demand_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_demand_line = $this->productUtil->changeDemandLineUnit($value, $business_id);
                $demand->demand_lines[$key] = $formated_demand_line;
            }
        }

        foreach ($demand->demand_lines as $key => $value) {
            $qty_available = $value->quantity - $value->quantity_sold - $value->quantity_adjusted;

            $demand->demand_lines[$key]->formatted_qty_available = $this->transactionUtil->num_f($qty_available);
        }

        return view('demand_return.add')
                    ->with(compact('demand'));
    }

    /**
     * Saves Purchase returns in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('demand.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $demand = Transaction::where('business_id', $business_id)
                        ->where('type', 'demand')
                        ->with(['demand_lines', 'demand_lines.sub_unit'])
                        ->findOrFail($request->input('transaction_id'));

            $return_quantities = $request->input('returns');
            $return_total = 0;

            DB::beginTransaction();

            foreach ($demand->demand_lines as $demand_line) {
                $old_return_qty = $demand_line->quantity_returned;

                $return_quantity = !empty($return_quantities[$demand_line->id]) ? $this->productUtil->num_uf($return_quantities[$demand_line->id]) : 0;

                $multiplier = 1;
                if (!empty($demand_line->sub_unit->base_unit_multiplier)) {
                    $multiplier = $demand_line->sub_unit->base_unit_multiplier;
                    $return_quantity = $return_quantity * $multiplier;
                }

                $demand_line->quantity_returned = $return_quantity;
                $demand_line->save();
                $return_total += $demand_line->demand_price_inc_tax * $demand_line->quantity_returned;

                //Decrease quantity in variation location details
                if ($old_return_qty != $demand_line->quantity_returned) {
                    $this->productUtil->decreaseProductQuantity(
                        $demand_line->product_id,
                        $demand_line->variation_id,
                        $demand->location_id,
                        $demand_line->quantity_returned,
                        $old_return_qty
                    );
                }
            }
            $return_total_inc_tax = $return_total + $request->input('tax_amount');

            $return_transaction_data = [
                'total_before_tax' => $return_total,
                'final_total' => $return_total_inc_tax,
                'tax_amount' => $request->input('tax_amount'),
                'tax_id' => $demand->tax_id
            ];

            if (empty($request->input('ref_no'))) {
                //Update reference count
                $ref_count = $this->transactionUtil->setAndGetReferenceCount('demand_return');
                $return_transaction_data['ref_no'] = $this->transactionUtil->generateReferenceNumber('demand_return', $ref_count);
            }
            
            $return_transaction = Transaction::where('business_id', $business_id)
                                            ->where('type', 'demand_return')
                                            ->where('return_parent_id', $demand->id)
                                            ->first();

            if (!empty($return_transaction)) {
                $return_transaction_before = $return_transaction->replicate();

                $return_transaction->update($return_transaction_data);

                $this->transactionUtil->activityLog($return_transaction, 'edited', $return_transaction_before);
            } else {
                $return_transaction_data['business_id'] = $business_id;
                $return_transaction_data['location_id'] = $demand->location_id;
                $return_transaction_data['type'] = 'demand_return';
                $return_transaction_data['status'] = 'final';
                $return_transaction_data['contact_id'] = $demand->contact_id;
                $return_transaction_data['transaction_date'] = \Carbon::now();
                $return_transaction_data['created_by'] = request()->session()->get('user.id');
                $return_transaction_data['return_parent_id'] = $demand->id;

                $return_transaction = Transaction::create($return_transaction_data);

                $this->transactionUtil->activityLog($return_transaction, 'added');
            }

            //update payment status
            $this->transactionUtil->updatePaymentStatus($return_transaction->id, $return_transaction->final_total);

            $output = ['success' => 1,
                            'msg' => __('lang_v1.demand_return_added_success')
                        ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect('demand-return')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('demand.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $demand = Transaction::where('business_id', $business_id)
                        ->with(['return_parent', 'return_parent.tax', 'demand_lines', 'contact', 'tax', 'demand_lines.sub_unit', 'demand_lines.product', 'demand_lines.product.unit'])
                        ->find($id);

        foreach ($demand->demand_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_demand_line = $this->productUtil->changeDemandLineUnit($value, $business_id);
                $demand->demand_lines[$key] = $formated_demand_line;
            }
        }
        
        $demand_taxes = [];
        if (!empty($demand->return_parent->tax)) {
            if ($demand->return_parent->tax->is_tax_group) {
                $demand_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($demand->return_parent->tax, $demand->return_parent->tax_amount));
            } else {
                $demand_taxes[$demand->return_parent->tax->name] = $demand->return_parent->tax_amount;
            }
        }

        //For combined demand return return_parent is empty
        if (empty($demand->return_parent) && !empty($demand->tax)) {
            if ($demand->tax->is_tax_group) {
                $demand_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($demand->tax, $demand->tax_amount));
            } else {
                $demand_taxes[$demand->tax->name] = $demand->tax_amount;
            }
        }

        // $activities = Activity::forSubject($demand->return_parent)
        //    ->with(['causer', 'subject'])
        //    ->latest()
        //    ->get();

        return view('demand_return.show')
                ->with(compact('demand', 'demand_taxes', 'activities'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('demand.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (request()->ajax()) {
                $business_id = request()->session()->get('user.business_id');

        
                $demand_return = Transaction::where('id', $id)
                                ->where('business_id', $business_id)
                                ->where('type', 'demand_return')
                                ->with(['demand_lines'])
                                ->first();
                
                DB::beginTransaction();

                if (empty($demand_return->return_parent_id)) {
                    $delete_demand_lines = $demand_return->demand_lines;
                    $delete_demand_line_ids = [];
                    foreach ($delete_demand_lines as $demand_line) {
                        $delete_demand_line_ids[] = $demand_line->id;
                        $this->productUtil->updateProductQuantity($demand_return->location_id, $demand_line->product_id, $demand_line->variation_id, $demand_line->quantity_returned, 0, null, false);
                    }
                    DemandLine::where('transaction_id', $demand_return->id)
                                ->whereIn('id', $delete_demand_line_ids)
                                ->delete();
                } else {
                    $parent_demand = Transaction::where('id', $demand_return->return_parent_id)
                                ->where('business_id', $business_id)
                                ->where('type', 'demand')
                                ->with(['demand_lines'])
                                ->first();

                    $updated_demand_lines = $parent_demand->demand_lines;
                    foreach ($updated_demand_lines as $demand_line) {
                        $this->productUtil->updateProductQuantity($parent_demand->location_id, $demand_line->product_id, $demand_line->variation_id, $demand_line->quantity_returned, 0, null, false);
                        $demand_line->quantity_returned = 0;
                        $demand_line->save();
                    }
                }

                //Delete Transaction
                $demand_return->delete();

                //Delete account transactions
                AccountTransaction::where('transaction_id', $id)->delete();

                DB::commit();

                $output = ['success' => true,
                            'msg' => __('lang_v1.deleted_success')
                        ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return $output;
    }
}
