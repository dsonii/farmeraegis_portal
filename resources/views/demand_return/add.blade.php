@extends('layouts.app')
@section('title', __('lang_v1.demand_return'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('lang_v1.demand_return')</h1>
</section>

<!-- Main content -->
<section class="content">
	{!! Form::open(['url' => action('PurchaseReturnController@store'), 'method' => 'post', 'id' => 'demand_return_form' ]) !!}
	{!! Form::hidden('transaction_id', $demand->id); !!}

	@component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.parent_demand')])
		<div class="row">
			<div class="col-sm-4">
				<strong>@lang('demand.ref_no'):</strong> {{ $demand->ref_no }} <br>
				<strong>@lang('messages.date'):</strong> {{@format_date($demand->transaction_date)}}
			</div>
			<div class="col-sm-4">
				<strong>@lang('demand.supplier'):</strong> {{ $demand->contact->name }} <br>
				<strong>@lang('demand.business_location'):</strong> {{ $demand->location->name }}
			</div>
		</div>
	@endcomponent

	@component('components.widget', ['class' => 'box-primary'])
		<div class="row">
			<div class="col-sm-4">
				<div class="form-group">
					{!! Form::label('ref_no', __('demand.ref_no').':') !!}
					{!! Form::text('ref_no', !empty($demand->return_parent->ref_no) ? $demand->return_parent->ref_no : null, ['class' => 'form-control']); !!}
				</div>
			</div>
			<div class="clearfix"></div>
			<hr>
			<div class="col-sm-12">
				<table class="table bg-gray" id="demand_return_table">
		          	<thead>
			            <tr class="bg-green">
			              	<th>#</th>
			              	<th>@lang('product.product_name')</th>
			              	<th>@lang('sale.unit_price')</th>
			              	<th>@lang('demand.demand_quantity')</th>
			              	<th>@lang('lang_v1.quantity_left')</th>
			              	<th>@lang('lang_v1.return_quantity')</th>
			              	<th>@lang('lang_v1.return_subtotal')</th>
			            </tr>
			        </thead>
			        <tbody>
			          	@foreach($demand->demand_lines as $demand_line)
			          	@php
			          		$unit_name = $demand_line->product->unit->short_name;

			          		$check_decimal = 'false';
			                if($demand_line->product->unit->allow_decimal == 0){
			                    $check_decimal = 'true';
			                }

			          		if(!empty($demand_line->sub_unit->base_unit_multiplier)) {
			          			$unit_name = $demand_line->sub_unit->short_name;

			          			if($demand_line->sub_unit->allow_decimal == 0){
			                    	$check_decimal = 'true';
			                	} else {
			                		$check_decimal = 'false';
			                	}
			          		}

			          		$qty_available = $demand_line->quantity - $demand_line->quantity_sold - $demand_line->quantity_adjusted;
			          	@endphp
			            <tr>
			              	<td>{{ $loop->iteration }}</td>
			              	<td>
			                	{{ $demand_line->product->name }}
			                 	@if( $demand_line->product->type == 'variable')
			                  	- {{ $demand_line->variations->product_variation->name}}
			                  	- {{ $demand_line->variations->name}}
			                 	@endif
			              	</td>
			              	<td><span class="display_currency" data-currency_symbol="true">{{ $demand_line->demand_price_inc_tax }}</span></td>
			              	<td><span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $demand_line->quantity }}</span> {{$unit_name}}</td>
			              	<td><span class="display_currency" data-currency_symbol="false" data-is_quantity="true">{{ $qty_available }}</span> {{$unit_name}}</td>
			              	<td>
			              		@php
					                $check_decimal = 'false';
					                if($demand_line->product->unit->allow_decimal == 0){
					                    $check_decimal = 'true';
					                }
					            @endphp
					            <input type="text" name="returns[{{$demand_line->id}}]" value="{{@format_quantity($demand_line->quantity_returned)}}"
					            class="form-control input-sm input_number return_qty input_quantity"
					            data-rule-abs_digit="{{$check_decimal}}" 
					            data-msg-abs_digit="@lang('lang_v1.decimal_value_not_allowed')"
					            @if($demand_line->product->enable_stock) 
			              			data-rule-max-value="{{$qty_available}}"
			              			data-msg-max-value="@lang('validation.custom-messages.quantity_not_available', ['qty' => $demand_line->formatted_qty_available, 'unit' => $unit_name ])" 
			              		@endif
					            >
					            <input type="hidden" class="unit_price" value="{{@num_format($demand_line->demand_price_inc_tax)}}">
			              	</td>
			              	<td>
			              		<div class="return_subtotal"></div>
			              		
			              	</td>
			            </tr>
			          	@endforeach
		          	</tbody>
		        </table>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-4">
				<strong>@lang('lang_v1.total_return_tax'): </strong>
				<span id="total_return_tax"></span> @if(!empty($demand->tax))({{$demand->tax->name}} - {{$demand->tax->amount}}%)@endif
				@php
					$tax_percent = 0;
					if(!empty($demand->tax)){
						$tax_percent = $demand->tax->amount;
					}
				@endphp
				{!! Form::hidden('tax_id', $demand->tax_id); !!}
				{!! Form::hidden('tax_amount', 0, ['id' => 'tax_amount']); !!}
				{!! Form::hidden('tax_percent', $tax_percent, ['id' => 'tax_percent']); !!}
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 text-right">
				<strong>@lang('lang_v1.return_total'): </strong>&nbsp;
				<span id="net_return">0</span> 
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-sm-12">
				<button type="submit" class="btn btn-primary pull-right">@lang('messages.save')</button>
			</div>
		</div>
	@endcomponent

	{!! Form::close() !!}

</section>
@stop
@section('javascript')
<script type="text/javascript">
	$(document).ready( function(){
		$('form#demand_return_form').validate();
		update_demand_return_total();
	});
	$(document).on('change', 'input.return_qty', function(){
		update_demand_return_total()
	});

	function update_demand_return_total(){
		var net_return = 0;
		$('table#demand_return_table tbody tr').each( function(){
			var quantity = __read_number($(this).find('input.return_qty'));
			var unit_price = __read_number($(this).find('input.unit_price'));
			var subtotal = quantity * unit_price;
			$(this).find('.return_subtotal').text(__currency_trans_from_en(subtotal, true));
			net_return += subtotal;
		});
		var tax_percent = $('input#tax_percent').val();
		var total_tax = __calculate_amount('percentage', tax_percent, net_return);
		var net_return_inc_tax = total_tax + net_return;

		$('input#tax_amount').val(total_tax);
		$('span#total_return_tax').text(__currency_trans_from_en(total_tax, true));
		$('span#net_return').text(__currency_trans_from_en(net_return_inc_tax, true));
	}
</script>
@endsection
