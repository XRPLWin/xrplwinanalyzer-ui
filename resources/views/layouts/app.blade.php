<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>XRPLWinAnalyzer UI</title>
        <meta name="title" content=">XRPLWin Analyzer">
        <meta name="description" content="Explore XRPLedger">
        <meta property="og:description" content="Explore XRPLedger">
        <meta property="twitter:description" content="Explore XRPLedger">

        <link rel="apple-touch-icon" sizes="180x180" href="/res/img/favicons/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/res/img/favicons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/res/img/favicons/favicon-16x16.png">
        <link rel="manifest" href="/res/img/favicons/site.webmanifest">
        <link rel="mask-icon" href="/res/img/favicons/safari-pinned-tab.svg" color="#5bbad5">
        <link rel="shortcut icon" href="/res/img/favicons/favicon.ico">
        <meta name="msapplication-TileColor" content="#da532c">
        <meta name="msapplication-config" content="/res/img/favicons/browserconfig.xml">
        <meta name="theme-color" content="#ffffff">

        <link rel="canonical" href="{{ URL::current() }}" />
        <meta name="color-scheme" content="dark light">

        <link href="{{ asset('res/lib/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('res/lib/bootstrap/offcanvas.css') }}" rel="stylesheet">
        <link href="{{ asset('res/lib/core/css.css?v='.md5(config('xwin.version'))) }}" rel="stylesheet">

        <link href="{{ asset('res/lib/core/media.css?v='.md5(config('xwin.version'))) }}" rel="stylesheet">
        <link href="{{ asset('res/lib/fa/css/all.min.css') }}" rel="stylesheet">
        <script>
          const xw_xrpl_wss_server = "{{config('xrpl.'.config('xrpl.net').'.server_wss')}}";
          const xw_analyzer_url = "{{config('xwin.analyzer_url_default')}}";
        </script>

        @yield('head')
    </head>
      <body class="@yield('class') antialiased bg-light">
        <main id="app">
          <nav class="navbar navbar-expand-md navbar-dark bg-dark" aria-label="Fourth navbar example">
             <div class="container-fluid">
               <a class="navbar-brand" href="/">
                  <img src="/res/img/xrplwin_logo_80.webp" alt="W" class="d-inline-block align-text-top" width="45" height="25" title="XRPLWin">
                  <span class="d-none d-lg-inline">XRPL Explorer</span>
                </a>
               <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav" aria-controls="topnav" aria-expanded="false" aria-label="Toggle navigation">
                 <span class="navbar-toggler-icon"></span>
               </button>

               <div class="collapse navbar-collapse" id="topnav">
                 <ul class="navbar-nav me-auto mb-2 mb-md-0">
                   {{--<li class="nav-item">
                     <a class="nav-link active" aria-current="page" href="/">Home</a>
                   </li>
                   <li class="nav-item">
                     <a class="nav-link" href="#">Link</a>
                   </li>
                   <li class="nav-item">
                     <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
                   </li>
                   <li class="nav-item dropdown">
                     <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-bs-toggle="dropdown" aria-expanded="false">Dropdown</a>
                     <ul class="dropdown-menu" aria-labelledby="dropdown04">
                       <li><a class="dropdown-item" href="#">Action</a></li>
                       <li><a class="dropdown-item" href="#">Another action</a></li>
                       <li><a class="dropdown-item" href="#">Something else here</a></li>
                     </ul>
                   </li>--}}
                 </ul>
                 <form method="GET" action="/search">
                   <input class="form-control" type="text" placeholder="Search" aria-label="Search" name="q">
                 </form>
               </div>
             </div>
           </nav>
          <div class="d-flex">
            <div class="d-flex flex-column flex-shrink-0 p-3 bg-white border-end border-bottom" style="width:280px">
              @yield('sidebar')
            </div>
            <div class="d-flex flex-column flex-fill">
              <div class="nav-scroller bg-body border-bottom">
               <nav class="nav nav-underline" aria-label="Secondary navigation">
                 @yield('topnav')
                 {{--<a class="nav-link active" aria-current="page" href="#">Info</a>
                 <a class="nav-link" aria-current="page" href="#">Assets</a>
                 <a class="nav-link" aria-current="page" href="#">Spending</a>
                 <a class="nav-link" href="#">Ancestry</a>
                 <a class="nav-link active" aria-current="page" href="#">Account<span class="badge bg-light text-dark rounded-pill align-text-bottom">0</span></a>--}}
               </nav>
             </div>
             <div class="p-3">
               @yield('content')
             </div>

            </div>
            <div class="d-flex flex-column flex-shrink-0 bg-white border-start sidebar  border-bottom" style="width:280px">
              <span class="text-muted text-uppercase m-2">Queue</span>
              <div id="sidebar_queue">...</div>
            </div>
          </div>
          {{--<div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0">
          <div class="container-fluid bg-dark"></div>
          </div>--}}
        </main>

        <div aria-live="polite" aria-atomic="true">
          <div class="toast-container position-fixed bottom-0 end-0 p-3 fixed" id="toast-container" style="z-index:1100">
            <!-- toasts are created dynamically -->
          </div>
          <div class="toast-container position-fixed p-3 top-0 start-50 translate-middle-x" id="toast-container-top">
            <!-- toasts are created dynamically -->
          </div>
        </div>


      <script src="{{ asset('res/lib/jquery/jquery.min.js') }}"></script>
      <script src="{{ asset('res/lib/bootstrap/bootstrap.min.js') }}"></script>
      <script src="{{ asset('res/lib/xrpl/xrpl.min.js') }}"></script>
      <script src="{{ asset('res/lib/core/js.js?v='.md5(config('xwin.version'))) }}"></script>
      <script src="{{ asset('res/lib/chartjs/chart.min.js') }}"></script>
      <script>
      XWAPIRawRequest({
        sysroute: xw_analyzer_url+'/server/queue',
        sysmethod:'GET',
        sysc:'rsqueue_cb'
      },'rsqueue')
      </script>
      @stack('javascript')
    </body>
</html>
