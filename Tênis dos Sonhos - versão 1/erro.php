<?php
session_start();
$mensagem_erro = isset($_GET['msg']) ? urldecode($_GET['msg']) : 'Ocorreu um erro inesperado.';
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Erro</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* ===== TAMANHO DA FONTE BASE ===== */
        html {
            font-size: 100%;
        }

        body {
            font-size: 1rem;
            background-color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            transition: background-color 0.3s ease, color 0.3s ease;
            margin: 0;
            padding: 20px;
        }

        /* ===== CONTROLES NO TOPO ===== */
        .controls-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px solid #dc3545;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .controls-container .btn-font {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.125rem;
            border: 2px solid #dc3545;
            color: #dc3545;
            background: white;
            transition: all 0.3s;
            cursor: pointer;
        }

        .controls-container .btn-font:hover {
            background: #dc3545;
            color: white;
        }

        .controls-container .font-percent {
            font-size: 1rem;
            font-weight: 700;
            color: #dc3545;
            min-width: 50px;
            text-align: center;
        }

        /* ===== ALAVANCA ===== */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 28px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #dc3545;
        }

        input:checked + .slider:before {
            transform: translateX(22px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        .font-size-display {
            font-size: 0.875rem;
            color: #333;
            font-weight: 600;
        }

        /* ===== WRAPPER DA TELA DE ERRO ===== */
        .wrapper {
            width: 480px;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 10px;
            border: 2px solid #dc3545;
            box-shadow: 0 0 30px rgba(220, 53, 69, 0.3);
            text-align: center;
            transition: all 0.3s ease;
        }

        .wrapper .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 20px;
        }

        .wrapper h4 {
            color: #dc3545;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .wrapper p {
            color: #333;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        /* ===== MODO ESCURO ===== */
        [data-bs-theme="dark"] body {
            background-color: #1a1a1a !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .wrapper {
            background-color: #2d2d2d !important;
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .wrapper h4,
        [data-bs-theme="dark"] .wrapper p {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .wrapper .error-icon {
            color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .controls-container {
            background: #2d2d2d !important;
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .controls-container .btn-font {
            background: #3d3d3d !important;
            color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .controls-container .btn-font:hover {
            background: #dc3545 !important;
            color: white !important;
        }

        [data-bs-theme="dark"] .controls-container .font-percent {
            color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .controls-container .font-size-display {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .btn-outline-danger {
            color: #ffffff !important;
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .btn-outline-danger:hover {
            background-color: #dc3545 !important;
            color: #ffffff !important;
        }

        /* ===== RESPONSIVO ===== */
        @media (max-width: 520px) {
            .wrapper, .controls-container {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <!-- ===== CONTROLES NO TOPO (MESMOS DO LOGIN.PHP) ===== -->
    <div class="controls-container">
        <span class="font-size-display">Tamanho:</span>
        <button class="btn-font" onclick="diminuirFonte()" title="Diminuir fonte">−</button>
        <span class="font-percent" id="fontPercent">100%</span>
        <button class="btn-font" onclick="aumentarFonte()" title="Aumentar fonte">+</button>

        <div style="width: 1px; height: 30px; background: #ddd;"></div>

        <span class="font-size-display" style="font-size: 0.875rem;">Modo:</span>
        <label class="switch">
            <input type="checkbox" id="themeToggle">
            <span class="slider round"></span>
        </label>
        <span id="themeStatus" style="font-size: 0.875rem; font-weight: 600; color: #333;">Claro</span>
    </div>

    <!-- ===== TELA DE ERRO ===== -->
    <div class="wrapper">
        <i class="fas fa-exclamation-triangle error-icon"></i>
        <h4>Ocorreu um Erro</h4>
        <p><?php echo htmlspecialchars($mensagem_erro); ?></p>
        <a href="login.php" class="btn btn-outline-danger btn-block btn-lg">
            <i class="fas fa-arrow-left"></i> Voltar para Login
        </a>
    </div>

    <!-- ===== SCRIPTS (MESMOS DO LOGIN.PHP) ===== -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // ===== ALTERNAR TEMA =====
        document.getElementById('themeToggle').addEventListener('change', function() {
            const htmlElement = document.documentElement;
            const themeStatus = document.getElementById('themeStatus');

            if (this.checked) {
                htmlElement.setAttribute('data-bs-theme', 'dark');
                themeStatus.textContent = 'Escuro';
                themeStatus.style.color = '#ffffff';
            } else {
                htmlElement.setAttribute('data-bs-theme', 'light');
                themeStatus.textContent = 'Claro';
                themeStatus.style.color = '#333';
            }
        });

        // ===== AUMENTAR E DIMINUIR FONTE =====
        let fontSize = 100;

        function aumentarFonte() {
            if (fontSize < 200) {
                fontSize += 10;
                aplicarFonte();
            }
        }

        function diminuirFonte() {
            if (fontSize > 60) {
                fontSize -= 10;
                aplicarFonte();
            }
        }

        function aplicarFonte() {
            document.documentElement.style.fontSize = fontSize + '%';
            document.getElementById('fontPercent').textContent = fontSize + '%';
            localStorage.setItem('fontSize', fontSize);
        }

        // Carregar tamanho salvo ao iniciar
        window.addEventListener('load', function() {
            const savedSize = localStorage.getItem('fontSize');
            if (savedSize) {
                fontSize = parseInt(savedSize);
                aplicarFonte();
            }
        });
    </script>
</body>
</html>