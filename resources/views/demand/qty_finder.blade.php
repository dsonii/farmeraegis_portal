@extends('layouts.app')
@section('title', __('demand.qty_finder'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('demand.qty_finder') <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('demand.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h1>
</section>

<!-- Main content -->
<section class="content">

	<!-- Page level currency setting -->
	<input type="hidden" id="p_code" value="{{$currency_details->code}}">
	<input type="hidden" id="p_symbol" value="{{$currency_details->symbol}}">
	<input type="hidden" id="p_thousand" value="{{$currency_details->thousand_separator}}">
	<input type="hidden" id="p_decimal" value="{{$currency_details->decimal_separator}}">

	@include('layouts.partials.error')

	{!! Form::open(['url' => action('DemandController@demandQtyFinder'), 'method' => 'post', 'id' => 'qty_finder_form' ]) !!}
	@component('components.widget', ['class' => 'box-primary'])
		<div class="row">
			@if(count($business_locations) == 1)
				@php 
					$default_location = current(array_keys($business_locations->toArray()));
					$search_disable = false; 
				@endphp
			@else
				@php $default_location = null;
				$search_disable = true;
				@endphp
			@endif
			<div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('location_id', __('demand.business_location').':*') !!}
					@show_tooltip(__('tooltip.demand_location'))
					{!! Form::select('location_ids', $business_locations, $default_location, ['class' => 'form-control select2', 'id' => 'location_ids', 'multiple', 'required'], $bl_attributes); !!}
				</div>
			</div>
			<div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('demand_order_ids', __('lang_v1.demand_order').':') !!}
					{!! Form::select('demand_order_ids[]', [], null, ['class' => 'form-control select2', 'multiple', 'id' => 'demand_order_ids']); !!}
				</div>
			</div>
			<div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('demand_product_ids', __('lang_v1.products').':') !!}
					{!! Form::select('demand_product_ids[]', [], null, ['class' => 'form-control select2', 'multiple', 'id' => 'demand_product_ids']); !!}
				</div>
			</div>
			<div class="col-sm-3">
                <div class="form-group">
                    <button type="button" id="submit_demand_form" class="btn btn-primary pull-right btn-flat">@lang('messages.print')</button>
                </div>
            </div>
		</div>
	@endcomponent

	@component('components.widget', ['class' => 'box-primary'])
		<div class="row">
			<div class="col-sm-8 col-sm-offset-2">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-search"></i>
						</span>
						{!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'), 'disabled' => $search_disable]); !!}
					</div>
				</div>
			</div>
		</div>
		@php
			$hide_tax = '';
			if( session()->get('business.enable_inline_tax') == 0){
				$hide_tax = 'hide';
			}
			$hide_field = 'hide';
		@endphp
		<div class="row">
			<div class="col-sm-12">
				<div class="table-responsive">
					<table class="table table-condensed table-bordered table-th-green text-center table-striped" id="demand_entry_table">
						<thead>
							<tr>
								<th>#</th>
								<th>@lang( 'product.product_name' )</th>
								<th class="{{$hide_field}}">@lang( 'demand.demand_quantity' )</th>
								<th class="{{$hide_field}}">@lang( 'lang_v1.unit_cost_before_discount' )</th>
								<th class="{{$hide_field}}">@lang( 'lang_v1.discount_percent' )</th>
								<th class="{{$hide_field}}">@lang( 'demand.unit_cost_before_tax' )</th>
								<th class="{{$hide_tax}}">@lang( 'demand.subtotal_before_tax' )</th>
								<th class="{{$hide_tax}}">@lang( 'demand.product_tax' )</th>
								<th class="{{$hide_tax}}">@lang( 'demand.net_cost' )</th>
								<th>@lang( 'demand.line_total' )</th>
								<th class="@if(!session('business.enable_editing_product_from_demand')) hide @endif">
									@lang( 'lang_v1.profit_margin' )
								</th>
								<th class="{{$hide_field}}">
									@lang( 'demand.unit_selling_price' )
									<small>(@lang('product.inc_of_tax'))</small>
								</th>
								@if(session('business.enable_lot_number'))
								<th class="{{$hide_field}}">
										@lang('lang_v1.lot_number')
									</th>
								@endif
								@if(session('business.enable_product_expiry'))
									<th class="{{$hide_field}}">
										@lang('product.mfg_date') / @lang('product.exp_date')
									</th>
								@endif
								<th><i class="fa fa-trash" aria-hidden="true"></i></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
				<hr/>
				<div class="pull-right col-md-5">
					<table class="pull-right col-md-12">
						<tr>
							<th class="col-md-7 text-right">@lang( 'lang_v1.total_items' ):</th>
							<td class="col-md-5 text-left">
								<span id="total_quantity" class="display_currency" data-currency_symbol="false"></span>
							</td>
						</tr>
						<tr class="hide">
							<th class="col-md-7 text-right">@lang( 'demand.total_before_tax' ):</th>
							<td class="col-md-5 text-left">
								<span id="total_st_before_tax" class="display_currency"></span>
								<input type="hidden" id="st_before_tax_input" value=0>
							</td>
						</tr>
						<tr>
							<th class="col-md-7 text-right {{$hide_field}}">@lang( 'demand.net_total_amount' ):</th>
							<td class="col-md-5 text-left {{$hide_field}}">
								<span id="total_subtotal" class="display_currency"></span>
								<!-- This is total before demand tax-->
								<input type="hidden" id="total_subtotal_input" value=0  name="total_before_tax">
							</td>
						</tr>
					</table>
				</div>

				<input type="hidden" id="row_count" value="0">
			</div>
		</div>
	@endcomponent

{!! Form::close() !!}
</section>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
<!-- /.content -->
@endsection

@section('javascript')
	<script src="{{ asset('js/demand.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script type="text/javascript">
		$(document).ready( function(){
      		__page_leave_confirmation('#qty_finder_form');
      		$('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });
    	});
    	$(document).on('change', '.payment_types_dropdown, #location_id', function(e) {
			
		    var default_accounts = $('select#location_id').length ? 
		                $('select#location_id')
		                .find(':selected')
		                .data('default_payment_accounts') : [];
		    var payment_types_dropdown = $('.payment_types_dropdown');
		    var payment_type = payment_types_dropdown.val();
		    var payment_row = payment_types_dropdown.closest('.payment_row');
	        var row_index = payment_row.find('.payment_row_index').val();

	        var account_dropdown = payment_row.find('select#account_' + row_index);
		    if (payment_type && payment_type != 'advance') {
		        var default_account = default_accounts && default_accounts[payment_type]['account'] ? 
		            default_accounts[payment_type]['account'] : '';
		        if (account_dropdown.length && default_accounts) {
		            account_dropdown.val(default_account);
		            account_dropdown.change();
		        }
		    }

		    if (payment_type == 'advance') {
		        if (account_dropdown) {
		            account_dropdown.prop('disabled', true);
		            account_dropdown.closest('.form-group').addClass('hide');
		        }
		    } else {
		        if (account_dropdown) {
		            account_dropdown.prop('disabled', false); 
		            account_dropdown.closest('.form-group').removeClass('hide');
		        }    
		    }
		});
	</script>
	@include('demand.partials.keyboard_shortcuts')
@endsection
