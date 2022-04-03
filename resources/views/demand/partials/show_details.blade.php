@php
  $hide_field = 'hide';
@endphp
<div class="modal-header">
    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    @php
      $title = $demand->type == 'demand' ? __('lang_v1.demand_details') : __('demand.demand_details');
    @endphp
    <h4 class="modal-title" id="modalTitle"> {{$title}} (<b>@lang('demand.ref_no'):</b> #{{ $demand->ref_no }})
    </h4>
</div>
<div class="modal-body">
  <div class="row">
    <div class="col-sm-12">
      <p class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($demand->transaction_date) }}</p>
    </div>
  </div>
  <div class="row invoice-info">
    <div class="col-sm-4 invoice-col">
      @lang('demand.supplier'):
      <address>
        {!! $demand->contact->contact_address !!}
        @if(!empty($demand->contact->tax_number))
          <br>@lang('contact.tax_no'): {{$demand->contact->tax_number}}
        @endif
        @if(!empty($demand->contact->mobile))
          <br>@lang('contact.mobile'): {{$demand->contact->mobile}}
        @endif
        @if(!empty($demand->contact->email))
          <br>@lang('business.email'): {{$demand->contact->email}}
        @endif
      </address>
      @if($demand->document_path)
        
        <a href="{{$demand->document_path}}" 
        download="{{$demand->document_name}}" class="btn btn-sm btn-success pull-left no-print">
          <i class="fa fa-download"></i> 
            &nbsp;{{ __('demand.download_document') }}
        </a>
      @endif
    </div>

    <div class="col-sm-4 invoice-col">
      @lang('business.business'):
      <address>
        <strong>{{ $demand->business->name }}</strong>
        {{ $demand->location->name }}
        @if(!empty($demand->location->landmark))
          <br>{{$demand->location->landmark}}
        @endif
        @if(!empty($demand->location->city) || !empty($demand->location->state) || !empty($demand->location->country))
          <br>{{implode(',', array_filter([$demand->location->city, $demand->location->state, $demand->location->country]))}}
        @endif
        
        @if(!empty($demand->business->tax_number_1))
          <br>{{$demand->business->tax_label_1}}: {{$demand->business->tax_number_1}}
        @endif

        @if(!empty($demand->business->tax_number_2))
          <br>{{$demand->business->tax_label_2}}: {{$demand->business->tax_number_2}}
        @endif

        @if(!empty($demand->location->mobile))
          <br>@lang('contact.mobile'): {{$demand->location->mobile}}
        @endif
        @if(!empty($demand->location->email))
          <br>@lang('business.email'): {{$demand->location->email}}
        @endif
      </address>
    </div>

    <div class="col-sm-4 invoice-col">
      <b>@lang('demand.ref_no'):</b> #{{ $demand->ref_no }}<br/>
      <b>@lang('messages.date'):</b> {{ @format_date($demand->transaction_date) }}<br/>
      @if(!empty($demand->status))
        <b>@lang('demand.demand_status'):</b> {{ __('lang_v1.' . $demand->status) }}<br>
      @endif
      @if(!empty($demand->payment_status))
      <b>@lang('demand.payment_status'):</b> {{ __('lang_v1.' . $demand->payment_status) }}<br>
      @endif
      @if(!empty($demand_nos))
            <strong>@lang('restaurant.order_no'):</strong>
            {{$demand_nos}}
        @endif

        @if(!empty($demand_dates))
            <br>
            <strong>@lang('lang_v1.order_dates'):</strong>
            {{$demand_dates}}
        @endif
      @if($demand->type == 'demand')
        @php
          $custom_labels = json_decode(session('business.custom_labels'), true);
        @endphp
        <strong>@lang('sale.shipping'):</strong>
        <span class="label @if(!empty($shipping_status_colors[$demand->shipping_status])) {{$shipping_status_colors[$demand->shipping_status]}} @else {{'bg-gray'}} @endif">{{$shipping_statuses[$demand->shipping_status] ?? '' }}</span><br>
        @if(!empty($demand->shipping_address()))
          {{$demand->shipping_address()}}
        @else
          {{$demand->shipping_address ?? '--'}}
        @endif
        @if(!empty($demand->delivered_to))
          <br><strong>@lang('lang_v1.delivered_to'): </strong> {{$demand->delivered_to}}
        @endif
        @if(!empty($demand->shipping_custom_field_1))
          <br><strong>{{$custom_labels['shipping']['custom_field_1'] ?? ''}}: </strong> {{$demand->shipping_custom_field_1}}
        @endif
        @if(!empty($demand->shipping_custom_field_2))
          <br><strong>{{$custom_labels['shipping']['custom_field_2'] ?? ''}}: </strong> {{$demand->shipping_custom_field_2}}
        @endif
        @if(!empty($demand->shipping_custom_field_3))
          <br><strong>{{$custom_labels['shipping']['custom_field_3'] ?? ''}}: </strong> {{$demand->shipping_custom_field_3}}
        @endif
        @if(!empty($demand->shipping_custom_field_4))
          <br><strong>{{$custom_labels['shipping']['custom_field_4'] ?? ''}}: </strong> {{$demand->shipping_custom_field_4}}
        @endif
        @if(!empty($demand->shipping_custom_field_5))
          <br><strong>{{$custom_labels['shipping']['custom_field_5'] ?? ''}}: </strong> {{$demand->shipping_custom_field_5}}
        @endif
        @php
          $medias = $demand->media->where('model_media_type', 'shipping_document')->all();
        @endphp
        @if(count($medias))
          @include('sell.partials.media_table', ['medias' => $medias])
        @endif
      @endif
    </div>
  </div>

  <br>
  <div class="row">
    <div class="col-sm-12 col-xs-12">
      <div class="table-responsive">
        <table class="table bg-gray">
          <thead>
            <tr class="bg-green">
              <th>#</th>
              <th>@lang('product.product_name')</th>
              <th>@lang('product.sku')</th>
              @if($demand->type == 'demand')
                <th>@lang( 'lang_v1.quantity_remaining' )</th>
              @endif
              <th>@if($demand->type == 'demand') @lang('lang_v1.dispatch_quantity') @else @lang('demand.demand_quantity') @endif</th>
              <th class="text-right {{$hide_field}}">@lang( 'lang_v1.unit_cost_before_discount' )</th>
              <th class="text-right {{$hide_field}}">@lang( 'lang_v1.discount_percent' )</th>
              <th class="no-print text-right {{$hide_field}}">@lang('demand.unit_cost_before_tax')</th>
              <th class="no-print text-right {{$hide_field}}">@lang('demand.subtotal_before_tax')</th>
              <th class="text-right {{$hide_field}}">@lang('sale.tax')</th>
              <th class="text-right {{$hide_field}}">@lang('demand.unit_cost_after_tax')</th>
              @if($demand->type != 'demand')
              <th class="text-right {{$hide_field}}">@lang('demand.unit_selling_price')</th>
              @if(session('business.enable_lot_number'))
                <th class="{{$hide_field}}">@lang('lang_v1.lot_number')</th>
              @endif
              @if(session('business.enable_product_expiry'))
                <th class="{{$hide_field}}">@lang('product.mfg_date')</th>
                <th class="{{$hide_field}}">@lang('product.exp_date')</th>
              @endif
              @endif
              <th class="text-right {{$hide_field}}">@lang('sale.subtotal')</th>
            </tr>
          </thead>
          @php 
            $total_before_tax = 0.00;
          @endphp
          @foreach($demand->demand_lines as $demand_line)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
                {{ $demand_line->product->name }}
                 @if( $demand_line->product->type == 'variable')
                  - {{ $demand_line->variations->product_variation->name}}
                  - {{ $demand_line->variations->name}}
                 @endif
              </td>
              <td>
                 @if( $demand_line->product->type == 'variable')
                  {{ $demand_line->variations->sub_sku}}
                  @else
                  {{ $demand_line->product->sku }}
                 @endif
              </td>
              @if($demand->type == 'demand')
              <td>
                <span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $demand_line->quantity - $demand_line->po_quantity_demandd }}</span> @if(!empty($demand_line->sub_unit)) {{$demand_line->sub_unit->short_name}} @else {{$demand_line->product->unit->short_name}} @endif
              </td>
              @endif
              <td><span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $demand_line->quantity }}</span> @if(!empty($demand_line->sub_unit)) {{$demand_line->sub_unit->short_name}} @else {{$demand_line->product->unit->short_name}} @endif</td>
              <td class="text-right {{$hide_field}}"><span class="display_currency" data-currency_symbol="true">{{ $demand_line->pp_without_discount}}</span></td>
              <td class="text-right {{$hide_field}}"><span class="display_currency">{{ $demand_line->discount_percent}}</span> %</td>
              <td class="no-print text-right {{$hide_field}}"><span class="display_currency" data-currency_symbol="true">{{ $demand_line->demand_price }}</span></td>
              <td class="no-print text-right {{$hide_field}}"><span class="display_currency" data-currency_symbol="true">{{ $demand_line->quantity * $demand_line->demand_price }}</span></td>
              <td class="text-right {{$hide_field}}"><span class="display_currency" data-currency_symbol="true">{{ $demand_line->item_tax }} </span> <br/><small>@if(!empty($taxes[$demand_line->tax_id])) ( {{ $taxes[$demand_line->tax_id]}} ) </small>@endif</td>
              <td class="text-right {{$hide_field}}"><span class="display_currency" data-currency_symbol="true">{{ $demand_line->demand_price_inc_tax }}</span></td>
              @if($demand->type != 'demand')
              @php
                $sp = $demand_line->variations->default_sell_price;
                if(!empty($demand_line->sub_unit->base_unit_multiplier)) {
                  $sp = $sp * $demand_line->sub_unit->base_unit_multiplier;
                }
              @endphp
              <td class="text-right {{$hide_field}}"><span class="display_currency" data-currency_symbol="true">{{$sp}}</span></td>

              @if(session('business.enable_lot_number'))
                <td class="{{$hide_field}}">{{$demand_line->lot_number}}</td>
              @endif

              @if(session('business.enable_product_expiry'))
              <td class="{{$hide_field}}">
                @if(!empty($demand_line->mfg_date))
                    {{ @format_date($demand_line->mfg_date) }}
                @endif
              </td>
              <td class="{{$hide_field}}">
                @if(!empty($demand_line->exp_date))
                    {{ @format_date($demand_line->exp_date) }}
                @endif
              </td>
              @endif
              @endif
              <td class="text-right {{$hide_field}}"><span class="display_currency" data-currency_symbol="true">{{ $demand_line->demand_price_inc_tax * $demand_line->quantity }}</span></td>
            </tr>
            @php 
              $total_before_tax += ($demand_line->quantity * $demand_line->demand_price);
            @endphp
          @endforeach
        </table>
      </div>
    </div>
  </div>
  <br>
  <div class="row">
    @if(!empty($demand->type == 'demand'))
    <div class="col-sm-12 col-xs-12">
      <h4>{{ __('sale.payment_info') }}:</h4>
    </div>
    <div class="col-md-6 col-sm-12 col-xs-12">
      <div class="table-responsive">
        <table class="table">
          <tr class="bg-green">
            <th>#</th>
            <th>{{ __('messages.date') }}</th>
            <th>{{ __('demand.ref_no') }}</th>
            <th>{{ __('sale.amount') }}</th>
            <th>{{ __('sale.payment_mode') }}</th>
            <th>{{ __('sale.payment_note') }}</th>
          </tr>
          @php
            $total_paid = 0;
          @endphp
          @forelse($demand->payment_lines as $payment_line)
            @php
              $total_paid += $payment_line->amount;
            @endphp
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ @format_date($payment_line->paid_on) }}</td>
              <td>{{ $payment_line->payment_ref_no }}</td>
              <td><span class="display_currency" data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
              <td>{{ $payment_methods[$payment_line->method] ?? '' }}</td>
              <td>@if($payment_line->note) 
                {{ ucfirst($payment_line->note) }}
                @else
                --
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center">
                @lang('demand.no_payments')
              </td>
            </tr>
          @endforelse
        </table>
      </div>
    </div>
    @endif
    <div class="col-md-6 col-sm-12 col-xs-12 @if($demand->type == 'demand') col-md-offset-6 @endif">
      <div class="table-responsive">
        <table class="table">
          <!-- <tr class="hide">
            <th>@lang('demand.total_before_tax'): </th>
            <td></td>
            <td><span class="display_currency pull-right">{{ $total_before_tax }}</span></td>
          </tr> -->
          <tr class="{{$hide_field}}">
            <th>@lang('demand.net_total_amount'): </th>
            <td></td>
            <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $total_before_tax }}</span></td>
          </tr>
          <tr class="{{$hide_field}}">
            <th>@lang('demand.discount'):</th>
            <td>
              <b>(-)</b>
              @if($demand->discount_type == 'percentage')
                ({{$demand->discount_amount}} %)
              @endif
            </td>
            <td>
              <span class="display_currency pull-right" data-currency_symbol="true">
                @if($demand->discount_type == 'percentage')
                  {{$demand->discount_amount * $total_before_tax / 100}}
                @else
                  {{$demand->discount_amount}}
                @endif                  
              </span>
            </td>
          </tr>
          <tr class="{{$hide_field}}">
            <th>@lang('demand.demand_tax'):</th>
            <td><b>(+)</b></td>
            <td class="text-right">
                @if(!empty($demand_taxes))
                  @foreach($demand_taxes as $k => $v)
                    <strong><small>{{$k}}</small></strong> - <span class="display_currency pull-right" data-currency_symbol="true">{{ $v }}</span><br>
                  @endforeach
                @else
                0.00
                @endif
              </td>
          </tr>
          @if( !empty( $demand->shipping_charges ) )
            <tr class="{{$hide_field}}">
              <th>@lang('demand.additional_shipping_charges'):</th>
              <td><b>(+)</b></td>
              <td><span class="display_currency pull-right" >{{ $demand->shipping_charges }}</span></td>
            </tr>
          @endif
          <tr class="{{$hide_field}}">
            <th>@lang('demand.demand_total'):</th>
            <td></td>
            <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ $demand->final_total }}</span></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-6">
      <strong>@lang('demand.shipping_details'):</strong><br>
      <p class="well well-sm no-shadow bg-gray">
        @if($demand->shipping_details)
          {{ $demand->shipping_details }}
        @else
          --
        @endif
      </p>
    </div>
    <div class="col-sm-6">
      <strong>@lang('demand.additional_notes'):</strong><br>
      <p class="well well-sm no-shadow bg-gray">
        @if($demand->additional_notes)
          {{ $demand->additional_notes }}
        @else
          --
        @endif
      </p>
    </div>
  </div>
  @if(!empty($activities))
  <div class="row">
    <div class="col-md-12">
          <strong>{{ __('lang_v1.activities') }}:</strong><br>
          @includeIf('activity_log.activities', ['activity_type' => 'demand'])
      </div>
  </div>
  @endif

  {{-- Barcode --}}
  <div class="row print_section">
    <div class="col-xs-12">
      <img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($demand->ref_no, 'C128', 2,30,array(39, 48, 54), true)}}">
    </div>
  </div>
</div>