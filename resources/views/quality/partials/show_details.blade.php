
  <div class="row">
    <div class="col-sm-12 col-xs-12">
      <div class="table-responsive">
        <table class="table bg-gray">
          <thead>
            <tr class="bg-green">
              <th>#</th>
              <th>@lang('product.product_name')</th>
              <th>@lang('product.sku')</th>
              @if($purchase->type == 'purchase_order')
                <th class="text-right">@lang( 'lang_v1.quantity_remaining' )</th>
              @endif
              <th class="text-right">@if($purchase->type == 'purchase_order') @lang('lang_v1.order_quantity') @else @lang('purchase.purchase_quantity') @endif</th>
              <th class="text-right">@lang( 'lang_v1.unit_cost_before_discount' )</th>
              <th class="text-right">@lang( 'lang_v1.discount_percent' )</th>
              <th class="no-print text-right">@lang('purchase.unit_cost_before_tax')</th>
              <th class="no-print text-right">@lang('purchase.subtotal_before_tax')</th>
              <th class="text-right">@lang('sale.tax')</th>
              <th class="text-right">@lang('purchase.unit_cost_after_tax')</th>
              @if($purchase->type != 'purchase_order')
              <th class="text-right">@lang('purchase.unit_selling_price')</th>
              @if(session('business.enable_lot_number'))
                <th>@lang('lang_v1.lot_number')</th>
              @endif
              @if(session('business.enable_product_expiry'))
                <th>@lang('product.mfg_date')</th>
                <th>@lang('product.exp_date')</th>
              @endif
              @endif
              <th class="text-right">@lang('sale.subtotal')</th>
              <th class="text-right">Quality Check</th>
            </tr>
          </thead>
          @php 
            $total_before_tax = 0.00;
          @endphp
          @foreach($purchase->purchase_lines as $purchase_line)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
                {{ $purchase_line->product->name }}
                 @if( $purchase_line->product->type == 'variable')
                  - {{ $purchase_line->variations->product_variation->name}}
                  - {{ $purchase_line->variations->name}}
                 @endif
              </td>
              <td>
                 @if( $purchase_line->product->type == 'variable')
                  {{ $purchase_line->variations->sub_sku}}
                  @else
                  {{ $purchase_line->product->sku }}
                 @endif
              </td>
              @if($purchase->type == 'purchase_order')
              <td>
                <span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $purchase_line->quantity - $purchase_line->po_quantity_purchased }}</span> @if(!empty($purchase_line->sub_unit)) {{$purchase_line->sub_unit->short_name}} @else {{$purchase_line->product->unit->short_name}} @endif
              </td>
              @endif
              <td><span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $purchase_line->quantity }}</span> @if(!empty($purchase_line->sub_unit)) {{$purchase_line->sub_unit->short_name}} @else {{$purchase_line->product->unit->short_name}} @endif</td>
              <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->pp_without_discount}}</span></td>
              <td class="text-right"><span class="display_currency">{{ $purchase_line->discount_percent}}</span> %</td>
              <td class="no-print text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->purchase_price }}</span></td>
              <td class="no-print text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->quantity * $purchase_line->purchase_price }}</span></td>
              <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->item_tax }} </span> <br/><small>@if(!empty($taxes[$purchase_line->tax_id])) ( {{ $taxes[$purchase_line->tax_id]}} ) </small>@endif</td>
              <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->purchase_price_inc_tax }}</span></td>
              @if($purchase->type != 'purchase_order')
              @php
                $sp = $purchase_line->variations->default_sell_price;
                if(!empty($purchase_line->sub_unit->base_unit_multiplier)) {
                  $sp = $sp * $purchase_line->sub_unit->base_unit_multiplier;
                }
              @endphp
              <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{$sp}}</span></td>

              @if(session('business.enable_lot_number'))
                <td>{{$purchase_line->lot_number}}</td>
              @endif

              @if(session('business.enable_product_expiry'))
              <td>
                @if(!empty($purchase_line->mfg_date))
                    {{ @format_date($purchase_line->mfg_date) }}
                @endif
              </td>
              <td>
                @if(!empty($purchase_line->exp_date))
                    {{ @format_date($purchase_line->exp_date) }}
                @endif
              </td>
              @endif
              @endif
              <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $purchase_line->purchase_price_inc_tax * $purchase_line->quantity }}</span></td>
            <td>
              <select name="product_quality_{{$purchase_line->id}}" onchange="save_transaction(this.name, this.value)">
                <option>--Select--</option>
                <option value="1" @if($purchase_line->quality_check=='1') selected @endif>Average</option>
                <option value="2" @if($purchase_line->quality_check=='2') selected @endif>Good</option>
                <option value="3" @if($purchase_line->quality_check=='3') selected @endif>Excellent</option>
                <option value="4" @if($purchase_line->quality_check=='4') selected @endif>Bad</option>
              </select>
            </td>
            </tr>
            @php 
           //   $total_before_tax += ($purchase_line->quantity * $purchase_line->purchase_price);
            @endphp
          @endforeach
        </table>
      </div>
    </div>
  </div>
  <br>
  