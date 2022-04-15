<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header">
		    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		    <h4 class="modal-title" id="modalTitle"> @lang('lang_v1.stock_transfer_details') (<b>@lang('purchase.ref_no'):</b> )
		    </h4>
		</div>
        {!! Form::open(['url' => action('StockTransferController@quantityFinderSave'), 'name'=>'quantity_finder', 'method' => 'post', 'id' => 'stock_transfer_form' ]) !!}
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                    <div class="table-responsive">
                        <table class="table bg-gray">
                        <tr class="bg-green">
                            <th>#</th>
                            <th>@lang('sale.product')</th>
                            <th>@lang('sale.demand_qty')</th>
                        </tr>
                        @php 
                            $total = 0.00;
                        @endphp
                        @foreach($array as $sell_lines)
                            <tr>
                                <input type="hidden" name="product[][{{ $sell_lines['product_id'] }}]" value="{{$sell_lines['demand_qty']}}">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $sell_lines['name'] }}
                            </td>
                            <td>{{ $sell_lines['demand_qty'] }}</td>
                            </tr>
                        @endforeach
                        </table>
                    </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="fa fa-share"></i> Share with Warehouse 
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
            </div>
        {!! Form::close() !!}
	</div>
</div>