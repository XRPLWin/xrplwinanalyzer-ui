@extends('layouts.app')
@section('sidebar')

<div>
  {{$account}}
</div>
@endsection
@section('topnav')
  <a class="nav-link active" aria-current="page" href="/account/{{$account}}">Overview</a>
  <a class="nav-link" aria-current="page" href="/account/{{$account}}/assets">Tokens
    <span class="badge bg-light text-dark rounded-pill align-text-bottom count-tokens">0</span>
  </a>
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
        <h3 class="fw-bold"><span id="price_total_xrp">...</span> XRP <span class="text-muted">≈ $<span id="price_total_fiat">0.00</span></span></h3>
        <h5 class="fw-bold mt-4">Assets</h5>
        <ul class="list-group assets">
          <li class="list-group-item">
            XRP <span class="float-end fw-bold"><span id="price_xrp">...</span> XRP</span>
          </li>
          <a href="/account/{{$account}}/assets" class="list-group-item list-group-item-action">
            Tokens <span class="float-end"><i class="fas fa-angle-right"></i></span>
            <span class="float-end me-3 fw-bold count-tokens">0</span>

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
          <dt class="col-lg-3 col-sm-3">Can receive XRP:</dt>  <dd class="col-lg-9 col-sm-9" id="account_receivexrp">-</dd>
          <dt class="col-lg-3 col-sm-3">Requires dest. tag:</dt>  <dd class="col-lg-9 col-sm-9" id="account_reqdesttag">-</dd>

          <dt class="col-lg-3 col-sm-3">Email hash:</dt><dd class="col-lg-9 col-sm-9 text-break" id="account_emailhash">-</dd>
          <dt class="col-lg-3 col-sm-3">Domain:</dt>  <dd class="col-lg-9 col-sm-9" id="account_domain">-</dd>
          <dt class="col-lg-3 col-sm-3">Access:</dt>
          <dd class="col-lg-9 col-sm-9">
            <div class="badge bg-success text-center d-none" style="letter-spacing:1px" id="li_yesblackholed">BLACKHOLED ACCOUNT</div>
            <div class="badge bg-info text-center d-none" style="letter-spacing:1px" id="li_noblackholed">NOT BLACKHOLED</div>
          </dd>
          <dt class="col-lg-3 col-sm-3">Rippling:</dt>  <dd class="col-lg-9 col-sm-9" id="account_rippling">-</dd>
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
  console.log('Rates:',exchangerates);
}

async function xw_xrpl_account_info() {
  sItem('sidebar_queue_local','connecting',{
    title: 'Connecting to XRPL...',
    descr: xw_xrpl_wss_server,
  });
  await xw_get_xrpl_client().connect();
  sItemChangeTitle('connecting','Connected');
  sItemAddClass('connecting','text-success',10000);


  const account_info_response = await xw_get_xrpl_client().request({
    "command": "account_info",
    "account": "{{$account}}",
    "strict": true,
    "ledger_index": "validated"
  });


  if(account_info_response.type == "response")
  {
    $("#price_xrp").text((account_info_response.result.account_data.Balance / 1000000));
    total_xrp = total_xrp.plus(new BigNumber((account_info_response.result.account_data.Balance / 1000000)));
    af = xrpl.parseAccountRootFlags(account_info_response.result.account_data.Flags);
    //console.log(af);
    if(account_info_response.result.account_data.RegularKey == "rrrrrrrrrrrrrrrrrrrrBZbvji" && af.lsfDisableMaster) {
      //Account is blackholed
      $("#li_yesblackholed").removeClass('d-none');
    } else $("#li_noblackholed").removeClass('d-none');

    if(account_info_response.result.account_data.Domain){
      $("#account_domain").text(xrpl.convertHexToString(account_info_response.result.account_data.Domain))
    }
    if(account_info_response.result.account_data.EmailHash){
      $("#account_emailhash").text(account_info_response.result.account_data.EmailHash)
    }
    //Rippling enabled:
    $("#account_rippling").text((af.lsfDefaultRipple)?'Enabled':'Disabled');
    //Can receive xrp
    $("#account_receivexrp").text((af.lsfDisallowXRP)?'No':'Yes');

    $("#account_reqdesttag").text((af.lsfRequireDestTag)?'Yes':'No');
  }

  xw_get_xrpl_client().disconnect();
  //sItemChangeTitle('connecting','Disconnected');
}
var XWAPI_account_lines_cb_total = 0;
var XWAPI_account_lines_cb_count = 0;
function XWAPI_account_lines_cb(d,el,sys,loader){
  XWAPI_account_lines_cb_total = d.result.lines.length;
  $(".count-tokens").text(XWAPI_account_lines_cb_total);
  sItemRemove('account_lines');
  sItem('sidebar_queue_local','account_lines',{title: 'Fetching balances...',descr:'0/'+XWAPI_account_lines_cb_count});
  //sItemChangeTitle('account_lines','Loaded',1000);
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
  //sItemRemove('account_lines');

}

function XWAPI_currency_rate_cb(d,el,sys,loader){
  XWAPI_account_lines_cb_count += 1;
  total_xrp = total_xrp.plus(BigNumber(account_lines[sys.sysaccount+'_'+sys.syscurrency].balance).times(BigNumber(d.price)));
  $("#price_total_xrp").text(total_xrp.toFormat(2));
  $("#price_total_fiat").text(total_xrp.times(exchangerates.usd).toFormat(2));
  sItemChangeSubTitle('account_lines',XWAPI_account_lines_cb_count+'/'+XWAPI_account_lines_cb_total+' '+sys.sysaccount);
  if(XWAPI_account_lines_cb_count >= XWAPI_account_lines_cb_total){
    sItemChangeTitle('account_lines','Balances fetched',4000);
    sItemAddClass('account_lines','text-success');
    //sItem('sidebar_queue_local','account_lines_done',{title: 'Balances fetched',descr:false,class:'text-success'});
  }

}

$(function(){
  xw_xrpl_account_info();
  sItem('sidebar_queue_local','account_lines',{title: 'Loading truslines...',descr:false,class:'text-success'});
  get_exchangerates();
  XWAPIRawRequest({
    sysroute: xw_analyzer_url+'/account_lines/{{$account}}',
    sysmethod:'GET',
    sysc:'account_lines_cb'
  },'account_lines')
});


</script>
@endpush
