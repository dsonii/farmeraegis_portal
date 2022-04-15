<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header">
		    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		    <h4 class="modal-title" id="modalTitle"> @lang('lang_v1.stock_transfer_details') (<b>@lang('purchase.ref_no'): {{$stock_transfers->refrence_number}}</b> )
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
                            <th>Warehouse Quantity</th>
                            <th>Purchase quantity </th>
                        </tr>
                        @php 
                            $total = 0.00;
                        @endphp
                        @foreach($stock_transfers->quantiy as $stock_transfer)
                            <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $stock_transfer->product->name }}
                            </td>
                            <td>{{ $stock_transfer->demand_quantity}}</td>
                            <td>
                            <input type="hidden" name="demand_quantity" class="form-control demand_quantity input_number demand_quantity-{{$loop->iteration}}" data-id="{{$stock_transfer->demand_quantity}}" value="{{!empty($stock_transfer->demand_quantity)?$stock_transfer->demand_quantity:0}}">    
                            <input type="text" name="ware_house_quantity" class="form-control ware_house_quantity input_number ware_house_quantity-{{$loop->iteration}}" data-itr="{{$loop->iteration}}" data-id="{{$stock_transfer->id}}" value="{{!empty($stock_transfer->ware_house_quantity)?$stock_transfer->ware_house_quantity:0}}"></td>
                            <td><input type="text" readonly name="purchase_quantity" class="form-control purchase_quantity input_number purchase_quantity-{{$loop->iteration}}" data-id="{{$stock_transfer->id}}" value="{{!empty($stock_transfer->purchase_quantity)?$stock_transfer->purchase_quantity:0}}"></td>
                            </tr>
                        @endforeach
                        </table>
                    </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
            </div>
        {!! Form::close() !!}
	</div>
</div>