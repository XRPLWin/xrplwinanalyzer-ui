var xw_xclient = null;
function xw_get_xrpl_client()
{
  if(xw_xclient === null)
    xw_xclient = new xrpl.Client("wss://"+xw_xrpl_wss_server+"/");
  return xw_xclient;
}
