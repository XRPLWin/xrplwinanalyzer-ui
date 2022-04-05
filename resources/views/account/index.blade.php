@extends('layouts.app')
@section('sidebar')

<div>
  {{$account}}
</div>
@endsection
@section('topnav')
  <a class="nav-link active" aria-current="page" href="/account/{{$account}}">Overview</a>
  <a class="nav-link d-none feature-type-normal" aria-current="page" href="/account/{{$account}}/tokens">Tokens
    <span class="badge bg-light text-dark rounded-pill align-text-bottom count-tokens"></span>
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
          <a href="/account/{{$account}}/tokens" class="list-group-item list-group-item-action d-none feature-type-normal">
            Tokens <span class="float-end"><i class="fas fa-angle-right"></i></span>
            <span class="float-end me-3 fw-bold count-tokens"></span>
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
          <dt class="col-lg-3 col-sm-3">Account type:</dt><dd class="col-lg-9 col-sm-9" id="account_type">-</dd>
          <dt class="col-lg-3 col-sm-3">Status:</dt><dd class="col-lg-9 col-sm-9" id="account_status">-</dd>
          <dt class="col-lg-3 col-sm-3">Activated:</dt><dd class="col-lg-9 col-sm-9">-</dd>
          <dt class="col-lg-3 col-sm-3">Activated by:</dt><dd class="col-lg-9 col-sm-9">-</dd>
          <dt class="col-lg-3 col-sm-3">Can receive XRP:</dt><dd class="col-lg-9 col-sm-9" id="account_receivexrp">-</dd>
          <dt class="col-lg-3 col-sm-3">Requires dest. tag:</dt><dd class="col-lg-9 col-sm-9" id="account_reqdesttag">-</dd>

          <dt class="col-lg-3 col-sm-3">Email hash:</dt><dd class="col-lg-9 col-sm-9 text-break" id="account_emailhash">-</dd>
          <dt class="col-lg-3 col-sm-3">Domain:</dt><dd class="col-lg-9 col-sm-9" id="account_domain">-</dd>
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
var account_type = null;


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
  $("#price_total_fiat").text(total_xrp.times(exchangerates.usd).toFormat(2));
}


function XWAPI_account_info_cb(d,el,sys,loader){
  sItemChangeTitle('account_info','Account info fetched');
  sItemChangeSubTitle('account_info',"Type: "+d.type);
  sItemAddClass('account_info','text-success',10000);
  account_type = d.type;

  $("#price_xrp").text((d.Balance / 1000000));
  total_xrp = total_xrp.plus(new BigNumber((d.Balance / 1000000)));
  $("#price_total_xrp").text(total_xrp.toFormat(2));
  $("#price_total_fiat").text(total_xrp.times(exchangerates.usd).toFormat(2));
  af = xrpl.parseAccountRootFlags(d.Flags);
  //console.log(af);
  if(d.RegularKey == "rrrrrrrrrrrrrrrrrrrrBZbvji" && af.lsfDisableMaster) {
    //Account is blackholed
    $("#li_yesblackholed").removeClass('d-none');
  } else $("#li_noblackholed").removeClass('d-none');

  if(d.Domain){
    $("#account_domain").text(xrpl.convertHexToString(d.Domain))
  }
  if(d.EmailHash){
    $("#account_emailhash").text(d.EmailHash)
  }
  //Rippling enabled:
  $("#account_rippling").text((af.lsfDefaultRipple)?'Enabled':'Disabled');
  //Can receive xrp
  $("#account_receivexrp").text((af.lsfDisallowXRP)?'No':'Yes');
  $("#account_reqdesttag").text((af.lsfRequireDestTag)?'Yes':'No');

  $("#account_type").text(d.type);

  if(d.synced)
    $("#account_status").text('Synced')
  else
    $("#account_status").text('Queued')

  if(account_type == 'normal') {
    //set UI
    $(".feature-type-normal").removeClass('d-none');
    //set UI end
    sItem('sidebar_queue_local','account_lines',{title: 'Loading truslines...',descr:false,class:'text-success'});
    XWAPIRawRequest({
      sysroute: xw_analyzer_url+'/account/trustlines/{{$account}}',
      sysmethod:'GET',
      sysc:'account_lines_cb'
    },'account_lines')
  } else if(account_type == 'issuer') {

  }
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

  sItem('sidebar_queue_local','account_info',{
    title: 'Fetching account info...',
    descr: '{{$account}}'
  });

  XWAPIRawRequest({
    sysroute: xw_analyzer_url+'/account/info/{{$account}}',
    sysmethod:'GET',
    sysc:'account_info_cb'
  },'account_lines');

  get_exchangerates();

});


</script>
@endpush
