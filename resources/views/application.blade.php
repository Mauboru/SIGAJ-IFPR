<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="{{ asset('images/logo/logo.ico') }}" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SIGAJ-IFPR - Sistema de Gestão Acadêmica</title>
  <link rel="stylesheet" type="text/css" href="{{ asset('loader.css') }}" />
  @vite(['resources/js/main.js'])
</head>
<body>
  <div id="app">
    <div id="loading-bg">
      <div class="loading-logo">
        <div>
          <h2 style="color: #4F46E5; font-size: 24px; font-weight: bold;">SIGAJ-IFPR</h2>
        </div>
      </div>
      <div class="loading">
        <div class="effect-1 effects"></div>
        <div class="effect-2 effects"></div>
        <div class="effect-3 effects"></div>
      </div>
    </div>
  </div>
</body>
</html>
