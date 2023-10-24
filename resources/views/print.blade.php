<!DOCTYPE html>
<html>
  <head>
    <title>{{ $pageTitle ?? 'Invoice' }}</title>

    <meta charset="utf-8">
    <meta name="format-detection" content="telephone=no">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name=viewport content="user-scalable=no,initial-scale=1,maximum-scale=1,minimum-scale=1,width=device-width">

    <link rel="icon" type="image/ico" href="/favicon.ico">

    <style type="text/css">
      * {
       box-sizing: border-box;
      }
      html, body, #q-app {
        width: 100%;
        direction: ltr;
        margin: 0;
      }

      body {
        min-width: 100px;
        min-height: 100%;
        font-family: Arial, sans-serif;
        -ms-text-size-adjust: 100%;
        -webkit-text-size-adjust: 100%;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        font-smoothing: antialiased;
        line-height: 1.5;
        font-size: 1rem;
      }

      {!! file_get_contents(public_path('/static-css/paper.css')) !!}
    </style>
  </head>
  <body>
    <div id="q-app">
      <div>
        <div class="form-print-preview">
          @yield('body')
        </div>
      </div>
    </div>
  </body>
</html>
