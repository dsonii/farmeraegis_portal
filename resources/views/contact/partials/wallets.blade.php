<table class="table table-striped dataTable no-footer" role="grid">
   <thead>
      <tr class="row-border blue-heading" role="row">
         <th class="text-center">Transaction Date</th>
         <th class="text-center">Transaction No</th>
         <th class="text-center">Type</th>
         <th class="text-center">Wallet Type</th>
         <th class="text-center">Amount</th>
         <th class="text-center">Notes</th>
      </tr>
   </thead>
   <tbody style="">
      @if($wallets)
         @foreach($wallets as $key => $wallet)
            <tr role="row" class="{{ $key / 2 == 0 ? 'even' : 'odd' }}">
               <th class="text-center">{{$wallet->created_at}}</th>
               <th class="text-center">{{$wallet->id}}</th>
               <th class="text-center">{{$wallet->type}}</th>
               <th class="text-center">{{$wallet->transaction_type}}</th>
               <th class="text-center">{{$wallet->final_total}}</th>
               <th class="text-center">{{$wallet->additional_notes}}</th>
            </tr>
         @endforeach
      @endif
   </tbody>
</table>