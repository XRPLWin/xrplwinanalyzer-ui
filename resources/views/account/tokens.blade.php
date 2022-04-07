@extends('layouts.app')
@section('sidebar')
<div>
  {{$account}}
</div>
@endsection
@section('topnav')
  <a class="nav-link" aria-current="page" href="/account/{{$account}}">Overview</a>
  <a class="nav-link active" aria-current="page" href="/account/{{$account}}/tokens">Tokens
    <span class="badge bg-light text-dark rounded-pill align-text-bottom count-tokens"></span>
  </a>
  <a class="nav-link" aria-current="page" href="/account/{{$account}}/nfts">NFTs</a>
  <a class="nav-link" aria-current="page" href="/account/{{$account}}/spending">Spending</a>
  <a class="nav-link" href="/account/{{$account}}/ancestry">Ancestry</a>
@endsection
@section('content')
  <h1 class="mb-3">Account tokens</h1>
  <div class="p-3 border rounded bg-white">
    <div class="row">
      <div class="col-6">
        <div class="text-muted text-uppercase fw-bold">Estimated token balance</div>
        <h3 class="fw-bold"><span id="price_total_xrp">...</span> XRP <span class="text-muted">≈ $<span id="price_total_fiat">0.00</span></span></h3>
      </div>
      <div class="col-6">
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Token</th>
              <th>Issuer</th>
              <th class="text-end">Holding amount</th>
              <th class="text-end">Value (USD)</th>
            </tr>
          </thead>
          <tbody id="trustlines"></tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
@push('javascript')
<script>
var total_xrp = new BigNumber(0);
var account_lines = [];
var exchangerates = {usd:0,eur:0};
function get_exchangerates(){
  //usd:
  XWAPIRawRequest({
    sysroute: xw_analyzer_url+'/currency_rates/USD+rhub8VRN55s94qWKDv6jmDy1pUykJzF3wq/XRP',
    sysmethod:'GET',
    syscurrency:'usd',
    sysc:'set_exchangerates'
  },'get_exchangerates_usd');
  //usd:
  XWAPIRawRequest({
    sysroute: xw_analyzer_url+'/currency_rates/EUR+rhub8VRN55s94qWKDv6jmDy1pUykJzF3wq/XRP',
    sysmethod:'GET',
    syscurrency:'eur',
    sysc:'set_exchangerates'
  },'get_exchangerates_eur')
}
function XWAPI_set_exchangerates(d,el,sys,loader){
  if(sys.syscurrency === 'usd') exchangerates.usd = d.price;
  if(sys.syscurrency === 'eur') exchangerates.eur = d.price;
}


var XWAPI_account_lines_cb_total = 0;
var XWAPI_account_lines_cb_count = 0;
function XWAPI_account_lines_cb(d,el,sys,loader){
  XWAPI_account_lines_cb_total = d.length;
  $(".count-tokens").text(XWAPI_account_lines_cb_total);
  sItemRemove('account_lines');
  sItem('sidebar_queue_local','account_lines',{title: 'Fetching balances...',descr:'0/'+XWAPI_account_lines_cb_count});
  //sItemChangeTitle('account_lines','Loaded',1000);

  $.each(d,function(k,v){

    var tr = '<tr>';
    tr += '<td>'+v.symbol+'<div class="text-muted small">'+v.currency+'</div></td>';
    tr += '<td class="font-monospace">'+v.account+' <a href="/account/'+v.account+'"><i class="fas fa-search"></i></a></td>';
    tr += '<td align="right">'+v.balance+'</td>';
    tr += '<td align="right" id="value_'+v.account+'_'+v.currency+'"></td>';
    tr += '</tr>';
    $("#trustlines").append(tr);
    account_lines[v.account+'_'+v.currency] = v;
    XWAPIRawRequest({
      sysroute: xw_analyzer_url+'/currency_rates/XRP/'+v.currency+'+'+v.account,
      sysmethod:'GET',
      syscurrency:v.currency,
      sysaccount:v.account,
      sysc:'currency_rate_cb'
    },'currency_rate_'+k);
  });
}

function XWAPI_currency_rate_cb(d,el,sys,loader){
  XWAPI_account_lines_cb_count += 1;
  var currenttokenvalue = BigNumber(account_lines[sys.sysaccount+'_'+sys.syscurrency].balance).times(BigNumber(d.price));
  $("#value_"+sys.sysaccount+'_'+sys.syscurrency).text(currenttokenvalue.toFormat(2));
  total_xrp = total_xrp.plus(currenttokenvalue);
  $("#price_total_xrp").text(total_xrp.toFormat(2));
  $("#price_total_fiat").text(total_xrp.times(exchangerates.usd).toFormat(2));
  sItemChangeSubTitle('account_lines',XWAPI_account_lines_cb_count+'/'+XWAPI_account_lines_cb_total+' '+sys.sysaccount);
  if(XWAPI_account_lines_cb_count >= XWAPI_account_lines_cb_total){
    sItemChangeTitle('account_lines','Balances fetched',4000);
    sItemAddClass('account_lines','text-success');
  }
}

$(function(){
  sItem('sidebar_queue_local','account_lines',{title: 'Loading truslines...',descr:false,class:'text-success'});
  get_exchangerates();
  XWAPIRawRequest({
    sysroute: xw_analyzer_url+'/account/trustlines/{{$account}}',
    sysmethod:'GET',
    sysc:'account_lines_cb'
  },'account_lines')
});


</script>
@endpush
