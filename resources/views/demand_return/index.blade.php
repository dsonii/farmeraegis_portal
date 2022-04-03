@extends('layouts.app')
@section('title', __('lang_v1.demand_return'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('lang_v1.demand_return')
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('demand_list_filter_location_id',  __('demand.business_location') . ':') !!}
                {!! Form::select('demand_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('demand_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('demand_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.all_demand_returns')])
        @can('demand.update')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action('CombinedDemandReturnController@create')}}">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
            @endslot
        @endcan
        @can('demand.view')
            @include('demand_return.partials.demand_return_list')
        @endcan
    @endcomponent

    <div class="modal fade payment_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>

<!-- /.content -->
@stop
@section('javascript')
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
<script>
    $(document).ready( function(){
        $('#demand_list_filter_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#demand_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
               demand_return_table.ajax.reload();
            }
        );
        $('#demand_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#demand_list_filter_date_range').val('');
            demand_return_table.ajax.reload();
        });

        //Demand table
        demand_return_table = $('#demand_return_datatable').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [[0, 'desc']],
            ajax: {
            url: '/demand-return',
            data: function(d) {
                if ($('#demand_list_filter_location_id').length) {
                    d.location_id = $('#demand_list_filter_location_id').val();
                }

                var start = '';
                var end = '';
                if ($('#demand_list_filter_date_range').val()) {
                    start = $('input#demand_list_filter_date_range')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');
                    end = $('input#demand_list_filter_date_range')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                }
                d.start_date = start;
                d.end_date = end;
            },
        },
            columnDefs: [ {
                "targets": [7, 8],
                "orderable": false,
                "searchable": false
            } ],
            columns: [
                { data: 'transaction_date', name: 'transaction_date'  },
                { data: 'ref_no', name: 'ref_no'},
                { data: 'parent_demand', name: 'T.ref_no'},
                { data: 'location_name', name: 'BS.name'},
                { data: 'name', name: 'contacts.name'},
                { data: 'payment_status', name: 'payment_status'},
                { data: 'final_total', name: 'final_total'},
                { data: 'payment_due', name: 'payment_due'},
                { data: 'action', name: 'action'}
            ],
            "fnDrawCallback": function (oSettings) {
                var total_demand = sum_table_col($('#demand_return_datatable'), 'final_total');
                $('#footer_demand_return_total').text(total_demand);
                
                $('#footer_payment_status_count').html(__sum_status_html($('#demand_return_datatable'), 'payment-status-label'));

                var total_due = sum_table_col($('#demand_return_datatable'), 'payment_due');
                $('#footer_total_due').text(total_due);
                
                __currency_convert_recursively($('#demand_return_datatable'));
            },
            createdRow: function( row, data, dataIndex ) {
                $( row ).find('td:eq(5)').attr('class', 'clickable_td');
            }
        });

        $(document).on(
        'change',
            '#demand_list_filter_location_id',
            function() {
                demand_return_table.ajax.reload();
            }
        );
    });
</script>
	
@endsection