$(document).ready(function() {
    $(document).on('keyup', '.ware_house_quantity', function(){
        var itr = $(this).data('itr');
        var id = $(this).data('id');
        var demandQty = $('.demand_quantity-'+itr).val();
        var warehouseQty  = $(this).val();
        var parsedDemandQty = parseInt(demandQty);
        var parsedWarehouseQty = parseInt(warehouseQty);
        var remaningQty = parsedDemandQty - parsedWarehouseQty;
        $('.purchase_quantity-'+itr).val(remaningQty);

       if(itr!='' && itr!=null) {
            $.ajax({
                method: 'POST',
                url: '/warehouse-quantity-save',
                data: {warehouseQty : warehouseQty, id:id, remaningQty:remaningQty},
                success: function(result) {
                    console.log("saved");
                },
            });
       }
    });

    //Date picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

    jQuery.validator.addMethod(
        'notEqual',
        function(value, element, param) {
            return this.optional(element) || value != param;
        },
        'Please select different location'
    );

    stock_transfer_table = $('#stock_transfer_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        ajax: '/warehouse-quantity',
        columns: [
            { data: 'created_at', name: 'created_at' },
            { data: 'refrence_number', name: 'refrence_number' }
        ],
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#stock_transfer_table'));
        },
    });
    var detailRows = [];
    // On each draw, loop over the `detailRows` array and show any child rows
    stock_transfer_table.on('draw', function() {
        $.each(detailRows, function(i, id) {
            $('#' + id + ' .view_stock_transfer').trigger('click');
        });
    });
});