$(document).ready(function() {
    // $(document).on('click', '.btn-quantity', function(){
    //     var array = []
    //     $(".input-icheck-box:checked").each(function(){
    //         array.push($(this).val());
    //     });
    //    if(array!='' && array!=null) {
    //         $.ajax({
    //             method: 'GET',
    //             url: '/quantity-finder',
    //             data: {keys : array},
    //             success: function(result) {
    //                 if (result) {
    //                     $('.view_modal')
    //                     .html(result)
    //                     .modal('show');
    //                 } 
    //             },
    //         });
    //    }
    // });
    // if ($('#search_product_for_srock_adjustment').length > 0) {
    //     //Add Product
    //     $('#search_product_for_srock_adjustment')
    //         .autocomplete({
    //             source: function(request, response) {
    //                 $.getJSON(
    //                     '/products/list',
    //                     { location_id: $('#location_id').val(), term: request.term },
    //                     response
    //                 );
    //             },
    //             minLength: 2,
    //             response: function(event, ui) {
    //                 if (ui.content.length == 1) {
    //                     ui.item = ui.content[0];
    //                     if (ui.item.qty_available > 0 && ui.item.enable_stock == 1) {
    //                         $(this)
    //                             .data('ui-autocomplete')
    //                             ._trigger('select', 'autocompleteselect', ui);
    //                         $(this).autocomplete('close');
    //                     }
    //                 } else if (ui.content.length == 0) {
    //                     swal(LANG.no_products_found);
    //                 }
    //             },
    //             focus: function(event, ui) {
    //                 if (ui.item.qty_available <= 0) {
    //                     return false;
    //                 }
    //             },
    //             select: function(event, ui) {
    //                 if (ui.item.qty_available > 0) {
    //                     $(this).val(null);
    //                     stock_transfer_product_row(ui.item.variation_id);
    //                 } else {
    //                     alert(LANG.out_of_stock);
    //                 }
    //             },
    //         })
    //         .autocomplete('instance')._renderItem = function(ul, item) {
    //         if (item.qty_available <= 0) {
    //             var string = '<li class="ui-state-disabled">' + item.name;
    //             if (item.type == 'variable') {
    //                 string += '-' + item.variation;
    //             }
    //             string += ' (' + item.sub_sku + ') (Out of stock) </li>';
    //             return $(string).appendTo(ul);
    //         } else if (item.enable_stock != 1) {
    //             return ul;
    //         } else {
    //             var string = '<div>' + item.name;
    //             if (item.type == 'variable') {
    //                 string += '-' + item.variation;
    //             }
    //             string += ' (' + item.sub_sku + ') </div>';
    //             return $('<li>')
    //                 .append(string)
    //                 .appendTo(ul);
    //         }
    //     };
    // }

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
        columnDefs: [
            {
                targets: [0, 1],
                orderable: false,
                searchable: false,
            },
        ],
        columns: [
            { data: 'date', name: 'date' },
            { data: 'refrence_number', name: 'refrence_number' }
        ],
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#stock_transfer_table'));
        },
    });
    var detailRows = [];

    $('#stock_transfer_table tbody').on('click', '.view_stock_transfer', function() {
        var tr = $(this).closest('tr');
        var row = stock_transfer_table.row(tr);
        var idx = $.inArray(tr.attr('id'), detailRows);

        if (row.child.isShown()) {
            $(this)
                .find('i')
                .removeClass('fa-eye')
                .addClass('fa-eye-slash');
            row.child.hide();

            // Remove from the 'open' array
            detailRows.splice(idx, 1);
        } else {
            $(this)
                .find('i')
                .removeClass('fa-eye-slash')
                .addClass('fa-eye');

            row.child(get_stock_transfer_details(row.data())).show();

            // Add to the 'open' array
            if (idx === -1) {
                detailRows.push(tr.attr('id'));
            }
        }
    });

    // On each draw, loop over the `detailRows` array and show any child rows
    stock_transfer_table.on('draw', function() {
        $.each(detailRows, function(i, id) {
            $('#' + id + ' .view_stock_transfer').trigger('click');
        });
    });

});


function stock_transfer_product_row(variation_id) {
    var row_index = parseInt($('#product_row_index').val());
    var location_id = $('select#location_id').val();
    $.ajax({
        method: 'POST',
        url: '/stock-adjustments/get_product_row',
        data: { row_index: row_index, variation_id: variation_id, location_id: location_id },
        dataType: 'html',
        success: function(result) {
            $('table#stock_adjustment_product_table tbody').append(result);
            update_table_total();
            $('#product_row_index').val(row_index + 1);
        },
    });
}



function get_stock_transfer_details(rowData) {
    var div = $('<div/>')
        .addClass('loading')
        .text('Loading...');
    $.ajax({
        url: '/stock-transfers/' + rowData.DT_RowId,
        dataType: 'html',
        success: function(data) {
            div.html(data).removeClass('loading');
        },
    });

    return div;
}


$(document).on('shown.bs.modal', '.view_modal', function() {
    __currency_convert_recursively($('.view_modal'));
});
