function initcontrol()
{
  $("body").off('click','.apipost').on('click','.apipost',function(){
        XWAPIFormRequest($(this));return false;
  });
  $("body").off('click','.apilink').on('click','.apilink',function(){
        XWAPIRequest($(this));return false;
  });

  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  });
}

$(function(){
  initcontrol();
});

var actiondisable = [];
var actiondisableperm = [];
function XWAPIFormRequest(el) {
  //get Form
  var $form = $(el).closest('form').addClass('processing');
  //get form elements and collect them
  var data = {};
  $.each($form.serializeArray(),function(i, v) {
    //it is multiselect
    if (v.name.match(/\[\]/)){if (!(v.name in data)) data[v.name] = []; data[v.name].push(v.value);}
    else data[v.name] = v.value;
  });
  XWAPIRequest(el,data);
}

function XWAPIRawRequest(data,token){
  if(!token) token = 'rawrequest';
  XWAPIRequest($('<a data-systoken="'+token+'" data-sysroute=""></a>'),data);
}
function XWAPIRequest(el,appenddata) {
    //open confirmation dialog
    if (typeof(el.data('sysconfirm')) !== 'undefined') {
        //var rc = xw_confirm(el,el.data('sysconfirm'),false);
        var rc = confirm(el.data('sysconfirm'));
        if (rc != true) return false;
    }
    //fetch token and route
    var hasErrors = false;
    var token = el.data('systoken');
    var route = el.data('sysroute');
    if(token==null){showToast('Programming error','APILINK data-systoken missing','red','bottomright');return false;}
    //stop request if one is already running
    if (actiondisable[token] && actiondisable[token] == 1)
        return false;
    if (actiondisableperm[token] && actiondisableperm[token] == 1)
        return false;
    actiondisable[token] = 1;
    var data = {};
    var sys = {};
    var loader = el.find("span.loader");
    if(loader.length)
    {
      loader.addClass('loading');
      //loader.html('<span><i class="fas fa-sync-alt fa-spin"></i></span>');
      loader.html('<span class="spinner-border spinner-border-sm" role="status"></span>')
    }
    else
      el.addClass('loading');

    var url = route;
    $.each(el.data(), function(i, v) {
        if (i.match("^sys")) sys[i] = v;
        else data[i] = v;
    });

    //appenddata to data
    if (typeof(appenddata) !== 'undefined') {
        jQuery.extend(data, appenddata);
    }
    $.each(data, function(i, v) {
        if (i.match("^sys")) sys[i] = v;
    });
    //if data route is defined then override elements data-sysroute
    if (appenddata && appenddata.sysroute) {
        route = appenddata.sysroute;
        url = route;
    }
    var dataf = {};
    $.each(data, function(i, v) {
        if (!i.match("^sys")) dataf[i] = v;
    });
    //error checking
    if(route==null){showToast('Programming error','APILINK data-sysroute missing', 'red','bottomright');return false;}
    $.ajax({
      type:sys.sysmethod ? sys.sysmethod : "POST",
      dataType: "json",
      url: url,
      data: dataf,
      beforeSend: function(xhr, opts){
        //izvrsi neku funkciju prije slanja
        if (sys.syscustbefore) {
          if(eval("typeof XWAPICustomBefore_"+sys.syscustbefore) !== "undefined"){
            //console.log("Call(syscustbefore) XWAPICustomBefore_"+sys.syscustbefore);
            window["XWAPICustomBefore_"+sys.syscustbefore](el,loader,xhr,opts);
          }
          else
            console.warn('Warning: Global Function named "XWAPICustomBefore_'+sys.syscustbefore+'(el,loader,xhr,opts)" does not exist in window scope.');
       }
      },
      success: function(d){
        actiondisable[token] = 0;
        loader.html('');
        loader.removeClass('loading');
        el.removeClass('loading');
        if (d.systitle) el.html('<i class="fas fa-check"></i> '+ d.systitle);
        if (d.sysstopnext == 1)
        {
            actiondisableperm[token] = 1;
            el.addClass('disabled');
        }
        if(d.msg) showToast(false,d.msg, 'green','topcenter');
        if(d.errormsg) showToast(false,d.errormsg, 'red','topcenter');
        if(d.infomsg) showToast(false,d.infomsg, 'blue','topcenter');
        //callback
        if (sys.sysc) {
          if(eval("typeof XWAPI_"+sys.sysc) !== "undefined"){
            //console.log('Call(sysc): XWAPI_'+sys.sysc);
            window["XWAPI_"+sys.sysc](d,el,sys,loader);
          }
          else
            console.warn('Warning: Global Function named "XWAPI_'+sys.sysc+'(d,el,sys,loader)" does not exist in window scope.');
        }
        else if (sys.syscust){
          if(eval("typeof XWAPICustom"+token+"_"+sys.syscust) !== "undefined"){
            //console.log("Call(syscust): "+"XWAPICustom"+token+"_"+sys.syscust)
            window["XWAPICustom"+token+"_"+sys.syscust](d,el,sys,loader)
          }
          else
            console.warn('Warning: Custom Function named "XWAPICustom'+token+'_'+sys.syscust+'(response,el,sys,loader)" does not exist in window scope.');
        }
      },
      error: function(a,d,c){
        if (typeof token !== 'undefined')
          actiondisable[token] = 0;
        loader.html('');
        loader.removeClass('loading');
        el.removeClass('loading');
        return XWAPIError(a,sys,el,loader,token);
      },
      //reinit control event on any newly created elements
      complete:function(){
        if(!sys.sysskipinitcontrol)
          initcontrol();
        var $form = $(el).closest('form');
        if($form.length)
          $form.removeClass('processing');
      }
    });
    return true;
}
function XWAPIError(response,sys,el,loader,token){
    //callback on error
    if (sys.sysc) {
      if(eval("typeof XWAPIError_"+sys.sysc) !== "undefined"){
        //console.log('Call(sysc): XWAPI_'+sys.sysc);
        window["XWAPIError_"+sys.sysc](response,el,loader);
      }
      else
        console.warn('Warning: Global Function named "XWAPIError_'+sys.sysc+'(response,el,loader)" does not exist in window scope.');
    }
    else if (sys.syscust){
      if(eval("typeof XWAPICustom"+token+"Error_"+sys.syscust) !== "undefined"){
        window["XWAPICustom"+token+"Error_"+sys.syscust](response,el,loader)
      }
      else
        console.warn('Warning: Custom Function named "XWAPICustom'+token+'Error_'+sys.syscust+'(response,el,loader)" does not exist in window scope.');
    }

    var r = response.responseJSON;
    if (response.status === 500)
    {
      showToast('500 Internal server error','Please contact server administrator', 'red','bottomright');
      return;
    }
    if (response.status === 402)
    {
      showToast(false,'402 Limit reached', 'red','bottomright');
      return;
    }
    if (response.status === 404)
    {
      showToast('404 Page not found','Please contact server administrator', 'red','bottomright');
      return;
    }
    if (response.status === 405)
    {
      showToast(false,'405 Command not allowed', 'red','bottomright');
      return;
    }
    if (response.status === 403)
    {
      showToast(false,'403 - Forbidden to execute this command', 'red','bottomright');
      return;
    }

    if (response.status === 419)
    {
      showToast('419 Unauthorized','Your session has expired', 'red','bottomright');
      return;
    }

    //handle form validation errors returned by Laravel validate()
    if (response.status === 422)
    {
      var collectederrors = '';
        $.each( r.errors, function(k,v) {

          $('label[for="'+k+'"]').addClass('text-danger').parent('.form-group').addClass('has-error').find('.form-control').addClass('is-invalid');
          collectederrors += v.join("<br />")+'<br />';
          //$('[name="'+k+'"]').after('<div class="invalid-feedback d-block">'+v.join("<br />")+'</div>');
          //$('label[for="'+k+'"]').after('<div class="invalid-feedback d-block">'+v.join("<br />")+'</div>');
        });
        showToast('Validation error',collectederrors, 'orange','topcenter');
        return;
    }

    var msg = r.description;
    if (r.erroraction == 'output')
    {
      showToast(false,r.msg, 'orange','topcenter');
      return false;
    }
    else //output
    {
        if (r.system == 1) //system message
          showToast(r.msg,r.data.reason, 'red','topcenter');
        else
          showToast(false,'Sorry, unable to complete request. Try again later.', 'red','topcenter');
        return false;
    }
}



/**
 * Usage: showToast('hey!', 'success');
 * color - red,green,orange,blue,white
 * position - bottomright,topcenter
 */
function showToast(title,msg,color,position,timeout) {
  css = '';
  icon = '<i class="fas fa-check"></i> &nbsp;&nbsp;'
  closebtncss = 'btn-close btn-close-white';
  if(color == 'white')
  {
    closebtncss = 'btn-close';
    css = '';
  }
  else if(color == 'green')
  {
    css = 'bg-success text-light';
  }
  else if(color == 'red')
  {
    css = 'bg-danger text-light';
    icon = '<i class="fas fa-times"></i> &nbsp;&nbsp;'
  }
  else if(color == 'orange')
  {
    css = 'bg-warning text-dark';
    icon = '<i class="fas fa-exclamation-triangle"></i> &nbsp;&nbsp;'
  }
  else if(color == 'blue')
  {
    css = 'bg-info text-light';
    icon = '<i class="fas fa-info-circle"></i> &nbsp;&nbsp;'
  }

  var delay = 7000;
  if(timeout)
     delay = timeout;
  var html = '<div class="toast '+css+' border-0" role="alert" aria-live="assertive" aria-atomic="true">';
  if(title)
  {
    html += '<div class="toast-header">'+icon+'<strong class="me-auto">'+title+'</strong><button type="button" class="'+closebtncss+'" data-bs-dismiss="toast" aria-label="Close"></button></div>';
  }
  html += '<div class="d-flex"><div class="toast-body">'+msg+'</div>';
  if(!title)
    html += '<button type="button" class="'+closebtncss+' me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div></div>';
  var toastElement = htmlToElement(html);
  var pos = 'toast-container';
  if(position == 'topcenter') pos = 'toast-container-top';
  var toastConainerElement = document.getElementById(pos);
  toastConainerElement.appendChild(toastElement);
  var toast = new bootstrap.Toast(toastElement, {delay:delay, animation:true});
  toast.show();
}
/**
 * @param {String} HTML representing a single element
 * @return {Element}
 */
function htmlToElement(html) {
    var template = document.createElement('template');
    html = html.trim(); // Never return a text node of whitespace as the result
    template.innerHTML = html;
    return template.content.firstChild;
}

function XWAPI_rsqueue_cb(d,el,loader){
  var h = '';
  $.each(d,function(k,v){
    if(v.qtype == 'account'){
      h += '<div class="sideber-queue-item border-top border-bottom p-2 sidebar-queue-item-account">';
      h +=  '<div class="font-monospace" title="'+v.qtype_data+'">'+v.qtype_data.substring(0,4)+'....'+v.qtype_data.slice(-4)+'</div>';
      h += '<span class="d-block">';
      if(v.attemts > 0)
        h += '<i class="fas fa-sync-alt fa-spin text-muted"></i> ';
      h += v.queue+'</span>';
      h += '</div>';
    }
  })
  $("#sidebar_queue").html(h);
}



// XRPL CLIENT
var xw_xclient = null;
function xw_get_xrpl_client()
{
  if(xw_xclient === null)
    xw_xclient = new xrpl.Client("wss://"+xw_xrpl_wss_server+"/");
  return xw_xclient;
}
