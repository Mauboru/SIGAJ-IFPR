<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="<?php echo e(asset('images/logo/infotech.ico')); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mempar</title>
  <link rel="stylesheet" type="text/css" href="<?php echo e(asset('loader.css')); ?>" />
  <?php echo app('Illuminate\Foundation\Vite')(['resources/js/main.js']); ?>
</head>

<body>
  <div id="app">
    <div id="loading-bg">
      <div class="loading-logo">
        <div>
          <img src="<?php echo e(asset('images/logo/infotech250x250.png')); ?>" alt="MEMPAR">
        </div>
      </div>
      <div class=" loading">
        <div class="effect-1 effects"></div>
        <div class="effect-2 effects"></div>
        <div class="effect-3 effects"></div>
      </div>
    </div>
  </div>

  <script>
    const loaderColor = localStorage.getItem('vuexy-initial-loader-bg') || '#FFFFFF'
    const primaryColor = localStorage.getItem('vuexy-initial-loader-color') || '#7367F0'

    if (loaderColor)
      document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)
    if (loaderColor)
      document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)

    if (primaryColor)
      document.documentElement.style.setProperty('--initial-loader-color', primaryColor)
  </script>
</body>

</html>
<?php /**PATH C:\Users\josue\Desktop\Mauboru\SISTEMAS\INFOTECH\MEMPAR-WEB\resources\views/application.blade.php ENDPATH**/ ?>