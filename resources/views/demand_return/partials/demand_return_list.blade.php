<div class="table-responsive">
    <table class="table table-bordered table-striped ajax_view" id="demand_return_datatable">
        <thead>
            <tr>
                <th>@lang('messages.date')</th>
                <th>@lang('demand.ref_no')</th>
                <th>@lang('lang_v1.parent_demand')</th>
                <th>@lang('demand.location')</th>
                <th>@lang('demand.supplier')</th>
                <th>@lang('demand.payment_status')</th>
                <th>@lang('demand.grand_total')</th>
                <th>@lang('demand.payment_due') &nbsp;&nbsp;<i class="fa fa-info-circle text-info" data-toggle="tooltip" data-placement="bottom" data-html="true" data-original-title="{{ __('messages.demand_due_tooltip')}}" aria-hidden="true"></i></th>
                <th>@lang('messages.action')</th>
            </tr>
        </thead>
        <tfoot>
            <tr class="bg-gray font-17 text-center footer-total">
                <td colspan="5"><strong>@lang('sale.total'):</strong></td>
                <td id="footer_payment_status_count"></td>
                <td><span class="display_currency" id="footer_demand_return_total" data-currency_symbol ="true"></span></td>
                <td><span class="display_currency" id="footer_total_due" data-currency_symbol ="true"></span></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>