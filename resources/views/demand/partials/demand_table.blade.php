@php
    $hide_field = 'hide';
@endphp
<table class="table table-bordered table-striped ajax_view" id="demand_table" style="width: 100%;">
    <thead>
        <tr>
            <th>@lang('messages.action')</th>
            <th>@lang('messages.date')</th>
            <th>@lang('demand.ref_no')</th>
            <th>@lang('demand.location')</th>
            <th>@lang('demand.supplier')</th>
            <th>@lang('demand.demand_status')</th>
            <th>@lang('demand.payment_status')</th>
            <th>@lang('demand.grand_total')</th>
            <th>@lang('demand.payment_due') &nbsp;&nbsp;<i class="fa fa-info-circle text-info no-print" data-toggle="tooltip" data-placement="bottom" data-html="true" data-original-title="{{ __('messages.demand_due_tooltip')}}" aria-hidden="true"></i></th>
            <th>@lang('lang_v1.added_by')</th>
        </tr>
    </thead>
    <tfoot class="{{$hide_field}}">
        <tr class="bg-gray font-17 text-center footer-total">
            <td colspan="5"><strong>@lang('sale.total'):</strong></td>
            <td class="footer_status_count"></td>
            <td class="footer_payment_status_count"></td>
            <td class="footer_demand_total"></td>
            <td class="text-left"><small>@lang('report.demand_due') - <span class="footer_total_due"></span><br>
            @lang('lang_v1.demand_return') - <span class="footer_total_demand_return_due"></span>
            </small></td>
            <td></td>
        </tr>
    </tfoot>
</table>