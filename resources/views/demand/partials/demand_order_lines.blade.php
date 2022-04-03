@foreach($demand_order->demand_lines as $demand_line)
	@if($demand_line->quantity - $demand_line->po_quantity_demandd > 0)
		@include('demand.partials.demand_entry_row', [
			'variations' => [$demand_line->variations],
			'product' => $demand_line->product,
			'row_count' => $row_count,
			'variation_id' => $demand_line->variation_id,
			'taxes' => $taxes,
			'currency_details' => $currency_details,
			'hide_tax' => $hide_tax,
			'sub_units' => $sub_units_array[$demand_line->id],
			'demand_order_line' => $demand_line,
			'demand_order' => $demand_order
		])
		@php
			$row_count++;
		@endphp
	@endif
@endforeach