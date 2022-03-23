@extends('layouts.app')
@section('sidebar')

<div>
  {{$account}}
</div>
@endsection
@section('content')

  <h1>{{$account}}</h1>
  <div class="p-2 border rounded bg-white">
    <div class="row">
      <div class="col-6">
        <span class="text-muted text-uppercase">Balance</span>
        <h3 class="text-success fw-bold"><span id="price_xrp">...</span> XRP</h3>
      </div>
      <div class="col-6">
        <dl class="row small my-0">
          <dt class="col-lg-3 col-sm-3">Email hash:</dt>
          <dd class="col-lg-9 col-sm-9 text-break">-</dd>
          <dt class="col-lg-3 col-sm-3">Domain:</dt>
          <dd class="col-lg-9 col-sm-9">-</dd>
          <dt class="col-lg-3 col-sm-3">Access:</dt>
          <dd class="col-lg-9 col-sm-9">
            <div class="badge bg-success text-center d-none" style="letter-spacing:1px" id="li_yesblackholed">BLACKHOLED ACCOUNT</div>
            <div class="badge bg-info text-center d-none" style="letter-spacing:1px" id="li_noblackholed">NOT BLACKHOLED</div>
          </dd>
        </dl>
      </div>
    </div>

    Info - general info
    Assets - trustlines
    Spending - graphs, filters etc incoming and outgoing payments in XRP

  </div>


@endsection
@push('javascript')
<script>

async function xw_xrpl_account_info() {
  await xw_get_xrpl_client().connect()
  const response = await xw_get_xrpl_client().request({
    "command": "account_info",
    "account": "{{$account}}",
    "strict": true,
    "ledger_index": "validated"
  });

  xw_get_xrpl_client().disconnect();
  console.log(response);
  if(response.type == "response")
  {
    $("#price_xrp").text((response.result.account_data.Balance / 1000000));
    af = xrpl.parseAccountRootFlags(response.result.account_data.Flags);
    if(response.result.account_data.RegularKey == "rrrrrrrrrrrrrrrrrrrrBZbvji" && af.lsfDisableMaster) {
      //Account is blackholed
      $("#li_yesblackholed").removeClass('d-none');
    } else $("#li_noblackholed").removeClass('d-none');
  }
}
  $(function(){
    xw_xrpl_account_info();
  });


</script>
@endpush
