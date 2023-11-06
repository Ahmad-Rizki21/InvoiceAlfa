<!DOCTYPE html>
<html>
  <head>
    <title>Web Console</title>

    <meta charset="utf-8">
    <meta name="description" content="<%= productDescription %>">
    <meta name="format-detection" content="telephone=no">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name=viewport content="user-scalable=no,initial-scale=1,maximum-scale=1,minimum-scale=1,width=device-width">
    {{-- <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width<% if (ctx.mode.cordova || ctx.mode.capacitor) { %>, viewport-fit=cover <% } %>"> --}}

    <link rel="icon" type="image/ico" href="/favicon.ico">

    <link rel="stylesheet" href="{{ quasar_asset('/css/vendor.css') }}" />
    <link rel="stylesheet" href="{{ asset('/static-css/paper.css') }}" />
    <link rel="stylesheet" href="{{ quasar_asset('/css/app.css') }}" />
  </head>
  <body>
    <!-- DO NOT touch the following DIV -->
    <div id="q-app"></div>

    <script type="text/javascript">var _APP_DATA = '{!! http_encrypt(json_encode([
      'url' => [
        'api' => url('/api'),
        'adapter_download' => config('services.invoice_printer_adapter.app_download_url'),
      ],
      'locale' => app()->getLocale(),
      'timezone' => config('app.timezone'),
      'debug' => config('app.debug'),
      'constants' => \App\Models\Constant::all(),
      'no' => csp_nonce(),
      'ws' => [
        'url' => config('services.websocket.url'),
        'src' => config('services.websocket.app_source'),
      ]
    ])) !!}';</script>
    <script type="text/javascript" src="{{ quasar_asset('/js/vendor.js') }}"></script>
    <script type="text/javascript" src="{{ quasar_asset('/js/app.js') }}"></script>
  </body>
</html>
