@extends('layouts.app')
@section('title', __('wallet.wallets'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('wallet.wallets')</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
                @if(auth()->user()->can('all_wallet.access'))
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                            {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2']); !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('customer_id',  __('contact.contact') . ':') !!}
                            {!! Form::select('customer_id', $contacts, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.all')]); !!}
                        </div>
                    </div>
                @endif
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('wallet.all_wallets')])
                @can('wallet.add')
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary" href="{{action('WalletController@create')}}">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot
                @endcan
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="wallet_table">
                        <thead>
                            <tr>
                                <th>@lang('messages.action')</th>
                                <th>@lang('messages.date')</th>
                                <th>Transaction Type</th>
                                <th>Wallet Type</th>
                                <th>@lang('sale.total_amount')</th>
                                <th>@lang('contact.contact')</th>
                                <th>Category</th>
                                <th>@lang('wallet.wallet_note')</th>
                                <th>@lang('lang_v1.added_by')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
 <script type="text/javascript">
    $(document).ready( function(){
        wallet_table = $('#wallet_table').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [[1, 'desc']],
            "ajax": {
                "url": "/wallets",
                "data": function ( d ) {
                    d.location_id = $('#location_id').val();
                    d.customer_id = $('#customer_id').val();
                }
            },
            scrollY:        "75vh",
            scrollX:        true,
            scrollCollapse: true,
            columns: [
                { data: 'action', name: 'action', orderable: false, "searchable": false},
                { data: 'transaction_date', name: 'transaction_date' , "searchable": false },
                { data: 'wallet_transaction_type', name: 'wallet_transaction_type', "searchable": false},
                { data: 'wallet_type', name: 'wallet_type', "searchable": false},
                { data: 'final_total', name: 'final_total', "searchable": false},
                { data: 'wallet_for', name: 'wallet_for', "searchable": false, "searchable": false},
                { data: 'category', name: 'category', "searchable": false},
                { data: 'additional_notes', name: 'additional_notes', "searchable": false},
                { data: 'created_by_admin', name: 'created_by_admin', "searchable": false},
            ],
        });
        
        $(document).on('change', '#location_id, #customer_id',  function() {
            wallet_table.ajax.reload();
        });
        
    });
    </script>
@endsection