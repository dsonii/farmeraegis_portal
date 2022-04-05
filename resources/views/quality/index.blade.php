@extends('layouts.app')
@section('title', __('purchase.purchases'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('purchase.purchases')
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
                {!! Form::label('Search','Search:') !!}
                {!! Form::text('search', null, ['placeholder' =>'Reference Number', 'class' => 'form-control', 'id'=>'search_text']); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <div style="margin-top: 23px;">
                    {!! Form::submit('Search', array('class' => 'btn btn-success', 'id' => 'search_submit')) !!}
                </div>
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => "Quality Check"])
    <div id="quality_table">
</div>
    @endcomponent



</section>

<!-- <section id="receipt_section" class="print_section"></section> -->

<!-- /.content -->
@stop
@section('javascript')
<script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
<script>
        //Date range as a button
    $(document).on('click', '#search_submit', function(e) {
        e.preventDefault();
        var search_text = $('#search_text').val();
        if (search_text=='') {
            alert('Please enter reference number');
            return false;
        }
        $.ajax({
            method: 'GET',
            url: '/quality-check',
            data: {'refrence_num':search_text},
            success: function(result) {
                 $('#quality_table').html(result);
            }
        });
    });
function save_transaction(name,value){
   var finalName =  name.replace('product_quality_',"");
   console.log(finalName);
   $.ajax({
        method: 'POST',
        url: '/quality-save',
        data: {'id':finalName, 'value':value},
        success: function(result) {
                console.log('data is saved');
        }
    });
}
</script>
	
@endsection