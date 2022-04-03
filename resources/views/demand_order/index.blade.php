@extends('layouts.app')
@section('title', __('lang_v1.demand_order'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('lang_v1.demand_order')
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('po_list_filter_location_id',  __('demand.business_location') . ':') !!}
                {!! Form::select('po_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('po_list_filter_supplier_id',  __('demand.supplier') . ':') !!}
                {!! Form::select('po_list_filter_supplier_id', $suppliers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('po_list_filter_status',  __('sale.status') . ':') !!}
                {!! Form::select('po_list_filter_status', $demandOrderStatuses, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        @if(!empty($shipping_statuses))
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('shipping_status', __('lang_v1.shipping_status') . ':') !!}
                    {!! Form::select('shipping_status', $shipping_statuses, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
        @endif
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('po_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('po_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.all_demand_orders')])
        @can('demand_order.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action('DemandOrderController@create')}}">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
            @endslot
        @endcan

        <table class="table table-bordered table-striped ajax_view" id="demand_order_table" style="width: 100%;">
            <thead>
                <tr>
                    <th>@lang('messages.action')</th>
                    <th>@lang('messages.date')</th>
                    <th>@lang('demand.ref_no')</th>
                    <th>@lang('demand.location')</th>
                    {{-- <th>@lang('demand.supplier')</th> --}}
                    <th>@lang('sale.status')</th>
                    <th>@lang('lang_v1.quantity_remaining')</th>
                    <th>@lang('lang_v1.shipping_status')</th>
                    <th>@lang('lang_v1.added_by')</th>
                </tr>
            </thead>
        </table>
    @endcomponent
    <div class="modal fade edit_pso_status_modal" tabindex="-1" role="dialog"></div>
</section>
<!-- /.content -->
@stop
@section('javascript')	
@includeIf('demand_order.common_js')
<script type="text/javascript">
    $(document).ready( function(){
        //Purchase table
        demand_order_table = $('#demand_order_table').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [[1, 'desc']],
            scrollY: "75vh",
            scrollX:        true,
            scrollCollapse: true,
            ajax: {
                url: '{{action("DemandOrderController@index")}}',
                data: function(d) {
                    if ($('#po_list_filter_location_id').length) {
                        d.location_id = $('#po_list_filter_location_id').val();
                    }
                    if ($('#po_list_filter_supplier_id').length) {
                        d.supplier_id = $('#po_list_filter_supplier_id').val();
                    }
                    if ($('#po_list_filter_status').length) {
                        d.status = $('#po_list_filter_status').val();
                    }
                    if ($('#shipping_status').length) {
                        d.shipping_status = $('#shipping_status').val();
                    }

                    var start = '';
                    var end = '';
                    if ($('#po_list_filter_date_range').val()) {
                        start = $('input#po_list_filter_date_range')
                            .data('daterangepicker')
                            .startDate.format('YYYY-MM-DD');
                        end = $('input#po_list_filter_date_range')
                            .data('daterangepicker')
                            .endDate.format('YYYY-MM-DD');
                    }
                    d.start_date = start;
                    d.end_date = end;

                    d = __datatable_ajax_callback(d);
                },
            },
            columns: [
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'ref_no', name: 'ref_no' },
                { data: 'location_name', name: 'BS.name' },
                // { data: 'name', name: 'contacts.name' },
                { data: 'status', name: 'transactions.status' },
                { data: 'po_qty_remaining', name: 'po_qty_remaining', "searchable": false},
                {data: 'shipping_status', name: 'transactions.shipping_status'},
                { data: 'added_by', name: 'u.first_name' }
            ]
        });

        $(document).on(
            'change',
            '#po_list_filter_location_id, #po_list_filter_supplier_id, #po_list_filter_status, #shipping_status',
            function() {
                demand_order_table.ajax.reload();
            }
        );

        $('#po_list_filter_date_range').daterangepicker(
        dateRangeSettings,
            function (start, end) {
                $('#po_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
               demand_order_table.ajax.reload();
            }
        );
        $('#po_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#po_list_filter_date_range').val('');
            demand_order_table.ajax.reload();
        });

        $(document).on('click', 'a.delete-purchase-order', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).attr('href');
                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                demand_order_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
    });
</script>
@endsection