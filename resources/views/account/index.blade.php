@extends('layouts.app')
@section('sidebar')

<div>
  {{$account}}
</div>
@endsection
@section('topnav')
  <a class="nav-link active" aria-current="page" href="/account/{{$account}}">Overview</a>
  <a class="nav-link" aria-current="page" href="/account/{{$account}}/assets">Tokens</a>
  <a class="nav-link" aria-current="page" href="/account/{{$account}}/nfts">NFTs</a>
  <a class="nav-link" aria-current="page" href="/account/{{$account}}/spending">Spending</a>
  <a class="nav-link" href="/account/{{$account}}/ancestry">Ancestry</a>
@endsection
@section('content')
  <h1 class="mb-3">Account overview</h1>
  <div class="p-3 border rounded bg-white">
    <div class="row">
      <div class="col-6">
        <div class="text-muted text-uppercase fw-bold">Estimated balance</div>
        <h3 class="fw-bold"><span id="price_total_xrp">...</span> XRP <span class="text-muted">≈ $0.00</span></h3>
        <h5 class="fw-bold mt-4">Assets</h5>
        <ul class="list-group assets">
          <li class="list-group-item">
            XRP <span class="float-end fw-bold"><span id="price_xrp">...</span> XRP</span>
          </li>
          <a href="/account/{{$account}}/assets" class="list-group-item list-group-item-action">
            Tokens <span class="float-end"><i class="fas fa-angle-right"></i></span>
          </a>
          <a href="/account/{{$account}}/nfts" class="list-group-item list-group-item-action">
            NFTs <span class="float-end"><i class="fas fa-angle-right"></i></span>
          </a>
        </ul>

        <div class="assets"></div>
      </div>
      <div class="col-6">
        <span class="text-muted text-uppercase">Account info</span>
        <dl class="row small mb-0 mt-3">
          <dt class="col-lg-3 col-sm-3">Activated:</dt>  <dd class="col-lg-9 col-sm-9">-</dd>
          <dt class="col-lg-3 col-sm-3">Activated by:</dt>  <dd class="col-lg-9 col-sm-9">-</dd>
          <dt class="col-lg-3 col-sm-3">Can receive XRP:</dt>  <dd class="col-lg-9 col-sm-9">?</dd>
          <dt class="col-lg-3 col-sm-3">Email hash:</dt><dd class="col-lg-9 col-sm-9 text-break">-</dd>
          <dt class="col-lg-3 col-sm-3">Domain:</dt>  <dd class="col-lg-9 col-sm-9">-</dd>
          <dt class="col-lg-3 col-sm-3">Access:</dt>
          <dd class="col-lg-9 col-sm-9">
            <div class="badge bg-success text-center d-none" style="letter-spacing:1px" id="li_yesblackholed">BLACKHOLED ACCOUNT</div>
            <div class="badge bg-info text-center d-none" style="letter-spacing:1px" id="li_noblackholed">NOT BLACKHOLED</div>
          </dd>
        </dl>
      </div>
    </div>
{{--
    Info - general info
    Assets - trustlines
    Spending - graphs, filters etc incoming and outgoing payments in XRP
--}}
  </div>


@endsection

@push('javascript')
<script>
/*
let a = new BigNumber('6001199760047990e-3');
let b = new BigNumber(12);
aa = a.plus(b);
alert(aa);
*/

var total_xrp = new BigNumber(0);
var account_lines = [];

async function xw_xrpl_account_info() {
  await xw_get_xrpl_client().connect()
  const response = await xw_get_xrpl_client().request({
    "command": "account_info",
    "account": "{{$account}}",
    "strict": true,
    "ledger_index": "validated"
  });
  xw_get_xrpl_client().disconnect();
  if(response.type == "response")
  {
    $("#price_xrp").text((response.result.account_data.Balance / 1000000));
    //total_xrp += (response.result.account_data.Balance / 1000000);
    total_xrp = total_xrp.plus(new BigNumber((response.result.account_data.Balance / 1000000)));
    af = xrpl.parseAccountRootFlags(response.result.account_data.Flags);
    if(response.result.account_data.RegularKey == "rrrrrrrrrrrrrrrrrrrrBZbvji" && af.lsfDisableMaster) {
      //Account is blackholed
      $("#li_yesblackholed").removeClass('d-none');
    } else $("#li_noblackholed").removeClass('d-none');
  }
}

function XWAPI_account_lines_cb(d,el,sys,loader){
  $.each(d.result.lines,function(k,v){
    account_lines[v.account+'_'+v.currency] = v;
    XWAPIRawRequest({
      sysroute: xw_analyzer_url+'/currency_rates/XRP/'+v.currency+'+'+v.account,
      sysmethod:'GET',
      syscurrency:v.currency,
      sysaccount:v.account,
      sysc:'currency_rate_cb'
    },'currency_rate_'+k)
  });
}

function XWAPI_currency_rate_cb(d,el,sys,loader){
  total_xrp = total_xrp.plus(BigNumber(account_lines[sys.sysaccount+'_'+sys.syscurrency].balance).times(BigNumber(d.price)));
  $("#price_total_xrp").text(total_xrp.toFixed(2));
}

$(function(){
  xw_xrpl_account_info();
  XWAPIRawRequest({
    sysroute: xw_analyzer_url+'/account_lines/{{$account}}',
    sysmethod:'GET',
    sysc:'account_lines_cb'
  },'account_lines')
});


</script>
@endpush
