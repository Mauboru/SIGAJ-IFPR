<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Erro 500 - Algo deu errado</title>
  <link rel="icon" href="<?php echo e(asset('images/logo/infotech.ico')); ?>" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: #f1f5f9;
      font-family: 'Segoe UI', Roboto, sans-serif;
      color: #1e293b;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      text-align: center;
      padding: 20px;
    }

    .error-code {
      font-size: 6rem;
      font-weight: bold;
      color: #ef4444;
    }

    .message {
      font-size: 1.5rem;
      margin-bottom: 20px;
    }

    .canvas-container {
      margin-top: 30px;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    canvas {
      background-color: #fff;
      border: 2px solid #334155;
    }

    .btn {
      margin-top: 20px;
      padding: 10px 20px;
      font-size: 1rem;
      background: #3b82f6;
      color: #fff;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
    }

    .btn:hover {
      background: #2563eb;
    }

    .game {
        border-bottom: 1px solid;
        height: 300px;
        width: 800px;
        position: relative;
        overflow: hidden;
    }

    .score {
        position: absolute;
        top: 30px;
        left: 0;
        font-size: x-large;
    }

    .dino {
        position: absolute;
        bottom: 0;
        background-image: url(<?php echo e(asset('images/dino.png')); ?>);
        width: 150px;
        height: 100px;
        background-size: 100px;
        background-repeat: no-repeat;
        transform: scaleX(-1);
    }

    .cacto {
        position: absolute;
        bottom: 0;
        background-image: url(<?php echo e(asset('images/cacto.png')); ?>);
        width: 35px;
        height: 70px;
        background-size: 35px;
        background-repeat: no-repeat;
        animation: cacto 3s linear infinite;
    }

    @keyframes cacto {
        0% {
            right: -20px;
        }
        100% {
            right: 850px;
        }
    }

    .jump {
        animation: jump 1.1s linear;
    }

    @keyframes jump {
        0% {
            bottom: 0;
        }

        30% {
            bottom: 80px;
        }

        50% {
            bottom: 100px;
        }

        80% {
            bottom: 80px;
        }

        100% {
            bottom: 0;
        }
    }
  </style>
</head>
<body>
    <div class="error-code">500</div>
    <div class="message">Algo deu errado. Enquanto isso, jogue um pouco!</div>
    <div class="game">
        <span class="score"></span>
        <div class="dino"></div>
        <div class="cacto"></div>
    </div>
    <a class="btn" href="<?php echo e(url('/')); ?>">Voltar ao início</a>
    <script src="script.js"></script>
  
    <script>
        const dino = document.querySelector(".dino");
        const cacto = document.querySelector(".cacto");
        const score = document.querySelector(".score");
        let alreadyJump = false;
        let count = 0;

        document.addEventListener("keydown", (e) => {
            if ((e.code === "ArrowUp") | (e.code === "Space")) {
                jump();
            }
        });

        function jump() {
            if (!dino.classList.contains("jump")) {
                dino.classList.add("jump");
                alreadyJump = true;

                setTimeout(() => {
                    dino.classList.remove("jump");
                    alreadyJump = false;
                }, 1100);
            }
        }

        setInterval(() => {
            let dinoBottom = parseInt(
                window.getComputedStyle(dino).getPropertyValue("bottom")
            );
            let cactoLeft = parseInt(
                window.getComputedStyle(cacto).getPropertyValue("left")
            );

            if (cactoLeft > 40 && cactoLeft < 120 && dinoBottom <= 50 && !alreadyJump) {
                alert(`Game Over! Seu score foi: ${count}`);
                count = 0;
            }

            count++;
            score.innerHTML = `SCORE: ${count}`;
        }, 150);
    </script>
</body>
</html>
<?php /**PATH C:\Users\josue\Desktop\Mauboru\InfoTech\MEMPAR-WEB\resources\views/errors/500.blade.php ENDPATH**/ ?>