@php
    $hide_field = 'hide';
@endphp
@foreach( $variations as $variation)
    <tr @if(!empty($demand_order_line)) data-demand_order_id="{{$demand_order_line->transaction_id}}" @endif>
        <td><span class="sr_number"></span></td>
        <td>
            {{ $product->name }} ({{$variation->sub_sku}})
            @if( $product->type == 'variable' )
                <br/>
                (<b>{{ $variation->product_variation->name }}</b> : {{ $variation->name }})
            @endif
            @if($product->enable_stock == 1)
                <br>
                <small class="text-muted" style="white-space: nowrap;">@lang('report.current_stock'): @if(!empty($variation->variation_location_details->first())) {{@num_format($variation->variation_location_details->first()->qty_available)}} @else 0 @endif {{ $product->unit->short_name }}</small>
            @endif
            
        </td>
        <td>
            @if(!empty($demand_order_line))
                {!! Form::hidden('demand[' . $row_count . '][demand_order_line_id]', $demand_order_line->id ); !!}
            @endif

            {!! Form::hidden('demand[' . $row_count . '][product_id]', $product->id ); !!}
            {!! Form::hidden('demand[' . $row_count . '][variation_id]', $variation->id , ['class' => 'hidden_variation_id']); !!}

            @php
                $check_decimal = 'false';
                if($product->unit->allow_decimal == 0){
                    $check_decimal = 'true';
                }
                $currency_precision = config('constants.currency_precision', 2);
                $quantity_precision = config('constants.quantity_precision', 2);

                $quantity_value = !empty($demand_order_line) ? $demand_order_line->quantity : 1;
                $max_quantity = !empty($demand_order_line) ? $demand_order_line->quantity - $demand_order_line->po_quantity_demandd : 0;
            @endphp
            
            <input type="text" 
                name="demand[{{$row_count}}][quantity]" 
                value="{{@format_quantity($quantity_value)}}"
                class="form-control input-sm demand_quantity input_number mousetrap"
                required
                data-rule-abs_digit={{$check_decimal}}
                data-msg-abs_digit="{{__('lang_v1.decimal_value_not_allowed')}}"
                @if(!empty($max_quantity))
                    data-rule-max-value="{{$max_quantity}}"
                    data-msg-max-value="{{__('lang_v1.max_quantity_quantity_allowed', ['quantity' => $max_quantity])}}" 
                @endif
            >


            <input type="hidden" class="base_unit_cost" value="{{$variation->default_demand_price}}">
            <input type="hidden" class="base_unit_selling_price" value="{{$variation->sell_price_inc_tax}}">

            <input type="hidden" name="demand[{{$row_count}}][product_unit_id]" value="{{$product->unit->id}}">
            @if(!empty($sub_units))
                <br>
                <select name="demand[{{$row_count}}][sub_unit_id]" class="form-control input-sm sub_unit">
                    @foreach($sub_units as $key => $value)
                        <option value="{{$key}}" data-multiplier="{{$value['multiplier']}}">
                            {{$value['name']}}
                        </option>
                    @endforeach
                </select>
            @else 
                {{ $product->unit->short_name }}
            @endif
        </td>
        <td class="{{$hide_field}}">
            @php
                $pp_without_discount = !empty($demand_order_line) ? $demand_order_line->pp_without_discount/$demand_order->exchange_rate : $variation->default_demand_price;

                $discount_percent = !empty($demand_order_line) ? $demand_order_line->discount_percent : 0;

                $demand_price = !empty($demand_order_line) ? $demand_order_line->demand_price/$demand_order->exchange_rate : $variation->default_demand_price;

                $tax_id = !empty($demand_order_line) ? $demand_order_line->tax_id : $product->tax;
            @endphp
            {!! Form::text('demand[' . $row_count . '][pp_without_discount]',
            number_format($pp_without_discount, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm demand_unit_cost_without_discount input_number', 'required']); !!}
        </td>
        <td class="{{$hide_field}}">
            {!! Form::text('demand[' . $row_count . '][discount_percent]', number_format($discount_percent, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm inline_discounts input_number', 'required']); !!}
        </td>
        <td class="{{$hide_field}}">
            {!! Form::text('demand[' . $row_count . '][demand_price]',
            number_format($demand_price, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm demand_unit_cost input_number', 'required']); !!}
        </td>
        <td class="{{$hide_tax}}">
            <span class="row_subtotal_before_tax display_currency">0</span>
            <input type="hidden" class="row_subtotal_before_tax_hidden" value=0>
        </td>
        <td class="{{$hide_tax}} {{$hide_field}}">
            <div class="input-group">
                <select name="demand[{{ $row_count }}][demand_line_tax_id]" class="form-control select2 input-sm demand_line_tax_id" placeholder="'Please Select'">
                    <option value="" data-tax_amount="0" @if( $hide_tax == 'hide' )
                    selected @endif >@lang('lang_v1.none')</option>
                    @foreach($taxes as $tax)
                        <option value="{{ $tax->id }}" data-tax_amount="{{ $tax->amount }}" @if( $tax_id == $tax->id && $hide_tax != 'hide') selected @endif >{{ $tax->name }}</option>
                    @endforeach
                </select>
                {!! Form::hidden('demand[' . $row_count . '][item_tax]', 0, ['class' => 'demand_product_unit_tax']); !!}
                <span class="input-group-addon demand_product_unit_tax_text">
                    0.00</span>
            </div>
        </td>
        <td class="{{$hide_tax}} {{$hide_field}}">
            @php
                $dpp_inc_tax = number_format($variation->dpp_inc_tax, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator);
                if($hide_tax == 'hide'){
                    $dpp_inc_tax = number_format($variation->default_demand_price, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator);
                }

                $dpp_inc_tax = !empty($demand_order_line) ? number_format($demand_order_line->demand_price_inc_tax/$demand_order->exchange_rate, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator) : $dpp_inc_tax;

            @endphp
            {!! Form::text('demand[' . $row_count . '][demand_price_inc_tax]', $dpp_inc_tax, ['class' => 'form-control input-sm demand_unit_cost_after_tax input_number', 'required']); !!}
        </td>
        <td class="{{$hide_field}}">
            <span class="row_subtotal_after_tax display_currency">0</span>
            <input type="hidden" class="row_subtotal_after_tax_hidden" value=0>
        </td>
        <td class="@if(!session('business.enable_editing_product_from_demand') || !empty($is_demand_order)) hide @endif">
            {!! Form::text('demand[' . $row_count . '][profit_percent]', number_format($variation->profit_percent, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm input_number profit_percent', 'required']); !!}
        </td>
        @if(empty($is_demand_order))
        <td class="{{$hide_field}}">
            @if(session('business.enable_editing_product_from_demand'))
                {!! Form::text('demand[' . $row_count . '][default_sell_price]', number_format($variation->sell_price_inc_tax, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator), ['class' => 'form-control input-sm input_number default_sell_price', 'required']); !!}
            @else
                {{ number_format($variation->sell_price_inc_tax, $currency_precision, $currency_details->decimal_separator, $currency_details->thousand_separator)}}
            @endif
        </td>
        @if(session('business.enable_lot_number'))
            <td class="{{$hide_field}}">
                {!! Form::text('demand[' . $row_count . '][lot_number]', null, ['class' => 'form-control input-sm']); !!}
            </td>
        @endif
        @if(session('business.enable_product_expiry'))
            <td style="text-align: left;" class="{{$hide_field}}">

                {{-- Maybe this condition for checkin expiry date need to be removed --}}
                @php
                    $expiry_period_type = !empty($product->expiry_period_type) ? $product->expiry_period_type : 'month';
                @endphp
                @if(!empty($expiry_period_type))
                <input type="hidden" class="row_product_expiry" value="{{ $product->expiry_period }}">
                <input type="hidden" class="row_product_expiry_type" value="{{ $expiry_period_type }}">

                @if(session('business.expiry_type') == 'add_manufacturing')
                    @php
                        $hide_mfg = false;
                    @endphp
                @else
                    @php
                        $hide_mfg = true;
                    @endphp
                @endif

                <b class="@if($hide_mfg) hide @endif"><small>@lang('product.mfg_date'):</small></b>
                <div class="input-group @if($hide_mfg) hide @endif">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    {!! Form::text('demand[' . $row_count . '][mfg_date]', null, ['class' => 'form-control input-sm expiry_datepicker mfg_date', 'readonly']); !!}
                </div>
                <b><small>@lang('product.exp_date'):</small></b>
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    {!! Form::text('demand[' . $row_count . '][exp_date]', null, ['class' => 'form-control input-sm expiry_datepicker exp_date', 'readonly']); !!}
                </div>
                @else
                <div class="text-center">
                    @lang('product.not_applicable')
                </div>
                @endif
            </td>
        @endif
        @endif
        <?php $row_count++ ;?>

        <td><i class="fa fa-times remove_demand_entry_row text-danger" title="Remove" style="cursor:pointer;"></i></td>
    </tr>
@endforeach

<input type="hidden" id="row_count" value="{{ $row_count }}">