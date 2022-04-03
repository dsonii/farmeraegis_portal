@extends('layouts.app')
@section('title', __('demand.demands'))

@section('content')
@php
    $hide_field = 'hide';
@endphp
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('demand.demands')
        <small></small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
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
                {!! Form::label('demand_list_filter_supplier_id',  __('demand.supplier') . ':') !!}
                {!! Form::select('demand_list_filter_supplier_id', $suppliers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('demand_list_filter_status',  __('demand.demand_status') . ':') !!}
                {!! Form::select('demand_list_filter_status', $orderStatuses, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3 {{$hide_field}}">
            <div class="form-group">
                {!! Form::label('demand_list_filter_payment_status',  __('demand.payment_status') . ':') !!}
                {!! Form::select('demand_list_filter_payment_status', ['paid' => __('lang_v1.paid'), 'due' => __('lang_v1.due'), 'partial' => __('lang_v1.partial'), 'overdue' => __('lang_v1.overdue')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('demand_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('demand_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __('demand.all_demands')])
        @can('demand.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action('DemandController@create')}}">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
            @endslot
        @endcan
        @include('demand.partials.demand_table')
    @endcomponent

    <div class="modal fade product_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade payment_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

    @include('demand.partials.update_demand_status_modal')

</section>

<section id="receipt_section" class="print_section"></section>

<!-- /.content -->
@stop
@section('javascript')
<script src="{{ asset('js/demand.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
<script>
        //Date range as a button
    $('#demand_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#demand_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
           demand_table.ajax.reload();
        }
    );
    $('#demand_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#demand_list_filter_date_range').val('');
        demand_table.ajax.reload();
    });

    $(document).on('click', '.update_status', function(e){
        e.preventDefault();
        $('#update_demand_status_form').find('#status').val($(this).data('status'));
        $('#update_demand_status_form').find('#demand_id').val($(this).data('demand_id'));
        $('#update_demand_status_modal').modal('show');
    });

    $(document).on('submit', '#update_demand_status_form', function(e){
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            beforeSend: function(xhr) {
                __disable_submit_button(form.find('button[type="submit"]'));
            },
            success: function(result) {
                if (result.success == true) {
                    $('#update_demand_status_modal').modal('hide');
                    toastr.success(result.msg);
                    demand_table.ajax.reload();
                    $('#update_demand_status_form')
                        .find('button[type="submit"]')
                        .attr('disabled', false);
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });
</script>
	
@endsection