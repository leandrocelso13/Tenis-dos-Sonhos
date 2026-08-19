<?php
session_start();
 
// Verifique se o usuário está logado e 2FA verificado
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || 
   !isset($_SESSION["2fa_verified"]) || $_SESSION["2fa_verified"] !== true){
    header("location: login.php");
    exit;
}

// BLOQUEAR usuários que SÃO master
if(isset($_SESSION["is_master"]) && $_SESSION["is_master"] === true){
    header("location: master.php");  // Master não pode entrar aqui!
    exit;
}

// Se chegou aqui, é usuário COMUM
$is_master = false;
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $is_master ? 'Painel Master' : 'Tênis dos Sonhos' ?> · Personalização Exclusiva</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        /*Preenche espaço*/
        * { margin: 0; padding: 0; box-sizing: border-box; }    

        /* Responsivo - Mobile (até 700px) */
        @media (max-width: 700px) {
            .card-profissional { padding: 2rem 1.5rem; border-radius: 28px; margin-top: -30px; }
            h1.titulo-principal { font-size: 2.4rem; }
            .subtitulo-exclusivo { font-size: 1.4rem; padding-left: 1rem; }
            p, .step-text { font-size: 1.05rem; }
            .step-number { width: 40px; height: 40px; font-size: 1.2rem; }
            .welcome-box { font-size: 1rem; }
            .carousel-item img { height: 300px; }
            .carousel-caption h3 { font-size: 1.2rem; }
            .carousel-caption p { font-size: 0.9rem; }
            .carousel-control-prev, .carousel-control-next { width: 35px; height: 35px; margin: 0 10px; }
            .master-cards-grid { grid-template-columns: 1fr; }
        }

        /*Navbar*/
        .btn { box-shadow: none !important; }
        .text-left-important { text-align: left !important; }
        .navbar-brand { font-weight: 600; }
        .navbar-dark .navbar-nav .nav-link { color: rgba(255,255,255,0.8); }
        .navbar-dark .navbar-nav .nav-link:hover { color: #fff; }

        /*Footer*/        
        footer {
            text-align: center;
            padding: 3px;
            background-color: #212529;
            color: white;
        }

        /* ===== CONTROLES DE FONTE ===== */
        .controls-container-navbar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-right: 10px;
            padding: 5px 10px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .controls-container-navbar .btn-font {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            border: 1px solid #dc3545;
            color: #ffffff;
            background: rgba(255,255,255,0.2);
            transition: all 0.3s;
            cursor: pointer;
            padding: 0;
        }

        .controls-container-navbar .btn-font:hover {
            background: #dc3545;
            color: white;
        }

        .controls-container-navbar .font-percent {
            font-size: 14px;
            font-weight: 700;
            color: #dc3545;
            min-width: 40px;
            text-align: center;
        }

        .controls-container-navbar .font-label {
            font-size: 12px;
            color: #ccc;
            font-weight: 600;
            margin-right: 2px;
        }

        /* ===== NAVBAR TOGGLER - SÍMBOLOS + E - NA COR BRANCA ===== */
        .navbar-toggler-icon {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* ===== ALAVANCA (TOGGLE) ===== */
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
            -webkit-transition: .4s;
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
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(22px);
            -ms-transform: translateX(22px);
            transform: translateX(22px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        /* ===== CORES DO MODO ESCURO ===== */
        [data-bs-theme="dark"] .slider {
            background-color: #555;
        }

        [data-bs-theme="dark"] .slider:before {
            background-color: #ddd;
        }

        [data-bs-theme="dark"] input:checked + .slider {
            background-color:  #dc3545;
        }

        /* ===== BORDAS VERMELHAS APENAS NO MODO ESCURO ===== */
        [data-bs-theme="dark"] .navbar {
            border-bottom: 2px solid #dc3545 !important;
        }

        [data-bs-theme="dark"] .container-fluid.bg-dark {
            border-top: 2px solid #dc3545 !important;
            border-bottom: 2px solid #dc3545 !important;
        }

        [data-bs-theme="dark"] footer {
            border-top: 2px solid #dc3545 !important;
        }

        /* ===== BORDAS VERMELHAS NAS CARDS NO MODO ESCURO ===== */
        [data-bs-theme="dark"] .card.border-danger {
            border-color: #dc3545 !important;
        }

        /* ===== TEXTO BRANCO NO MODO ESCURO ===== */
        [data-bs-theme="dark"] body,
        [data-bs-theme="dark"] h1, [data-bs-theme="dark"] h2, [data-bs-theme="dark"] h3, 
        [data-bs-theme="dark"] h4, [data-bs-theme="dark"] h5, [data-bs-theme="dark"] h6,
        [data-bs-theme="dark"] p, [data-bs-theme="dark"] span, [data-bs-theme="dark"] div,
        [data-bs-theme="dark"] label, [data-bs-theme="dark"] a,
        [data-bs-theme="dark"] .h1, [data-bs-theme="dark"] .h2, [data-bs-theme="dark"] .h3,
        [data-bs-theme="dark"] .h4, [data-bs-theme="dark"] .h5, [data-bs-theme="dark"] .h6,
        [data-bs-theme="dark"] .card-body, [data-bs-theme="dark"] .card-title,
        [data-bs-theme="dark"] .card-text, [data-bs-theme="dark"] .btn {
            color: #FFFFFF !important;
        }

        [data-bs-theme="dark"] .card {
            background-color: #333 !important;
            border-color: #666 !important;
        }

        [data-bs-theme="dark"] body {
            background-color: #1a1a1a !important;
        }

        /* ===== MODO ESCURO - CONTROLES DA NAVBAR ===== */
        [data-bs-theme="dark"] .controls-container-navbar {
            background: rgba(255,255,255,0.05);
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .controls-container-navbar .btn-font {
            color: #dc3545;
            border-color: #dc3545;
            background: rgba(255,255,255,0.1);
        }

        [data-bs-theme="dark"] .controls-container-navbar .btn-font:hover {
            background: #dc3545;
            color: white;
        }

        [data-bs-theme="dark"] .controls-container-navbar .font-percent {
            color: #dc3545;
        }

        [data-bs-theme="dark"] .controls-container-navbar .font-label {
            color: #ccc;
        }

        /* ===== ESTILO DA BARRA DE PESQUISA NA NAVBAR ===== */
        .navbar-search-form {
            display: flex;
            width: 100%;
            max-width: 400px;
            margin: 0 15px;
        }

        .navbar-search-form .form-control {
            border-radius: 50px 0 0 50px;
            border: 1px solid #dc3545;
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        .navbar-search-form .form-control::placeholder {
            color: #ccc;
        }

        .navbar-search-form .btn {
            border-radius: 0 50px 50px 0;
            border: 1px solid #dc3545;
            border-left: none;
            background: #dc3545;
            color: white;
        }

        /* Ajuste da caixa de pesquisa quando a navbar colapsar no mobile */
        @media (max-width: 991px) {
            .navbar-search-form {
                max-width: 100%;
                margin: 10px 0;
            }
        }

        /* ===== ESTILO DO CARD SOBRE MIM (2 COLUNAS) ===== */
        .card-sobre-mim {
            border: 2px solid #dc3545;
            border-radius: 15px;
            overflow: hidden;
            background: #fff;
            display: flex;
            flex-direction: row; /* Coloca foto e texto lado a lado */
            max-width: 900px;
            margin: 0 auto;
        }

        /* Coluna da foto (metade esquerda) */
        .col-foto {
            flex: 1; /* Ocupa metade do espaço */
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            padding: 20px;
        }

        .col-foto img {
            width: 100%;
            height: auto; /* Garante que a imagem NÃO seja cortada */
            max-height: 500px;
            object-fit: contain; /* Mostra a imagem inteira sem distorcer */
            border-radius: 10px;
        }

        /* Coluna do texto (metade direita) - CORRIGIDA COM MAIS ESPAÇO */
        .col-texto {
            flex: 1; /* Ocupa a outra metade */
            padding: 50px 40px; /* AUMENTEI O PADDING (mais espaço nas bordas) */
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #fff;
        }

        .col-texto h2 {
            color: #dc3545;
            margin-bottom: 25px; /* Mais espaço abaixo do título */
            font-weight: bold;
        }

        .col-texto p {
            color: #333;
            font-size: 1.1rem;
            line-height: 2.0; /* AUMENTEI O ESPAÇO ENTRE AS LINHAS */
            margin-bottom: 20px; /* Mais espaço entre um parágrafo e outro */
            text-align: justify; /* TEXTO JUSTIFICADO */
        }

        /* ===== CORREÇÃO DO MODO ESCURO PARA O CARD ===== */
        [data-bs-theme="dark"] .card-sobre-mim {
            background: #2d2d2d !important;
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .col-foto {
            background: #1a1a1a !important;
        }

        [data-bs-theme="dark"] .col-texto {
            background: #2d2d2d !important;
        }

        [data-bs-theme="dark"] .col-texto h2 {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .col-texto p {
            color: #ffffff !important; /* TEXTO BRANCO PURO */
            font-weight: 500 !important; /* Deixa o branco mais forte */
            text-align: left !important; /* Tira o Justify e deixa alinhado à esquerda */
        }

        [data-bs-theme="dark"] .col-texto i {
            color: #dc3545 !important;
        }

        /* Responsividade para celular (empilha um embaixo do outro) */
        @media (max-width: 768px) {
            .card-sobre-mim {
                flex-direction: column !important;
            }
            .col-foto, .col-texto {
                flex: none;
                width: 100%;
            }
            .col-foto img {
                max-height: 300px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">        
       👟 Tênis dos Sonhos       
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
      <span class="navbar-toggler-icon"></span>
    </button>   
    
    <div class="collapse navbar-collapse" id="mynavbar">
      
      <!-- ===== LADO ESQUERDO: Links de navegação ===== -->
      <ul class="navbar-nav me-auto">       
        <li class="nav-item">
          <a class="nav-link" href="main.php">Início</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="produtos.php">Produtos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="sobre.php">Sobre Mim</a>
        </li>
      </ul>
      
      <!-- ===== LADO DIREITO: Controles e Conta ===== -->
      <ul class="navbar-nav ms-auto align-items-center">
        
        <!-- Controles de fonte -->
        <li class="nav-item me-2">
            <div class="controls-container-navbar">
                <span class="font-label">Tamanho:</span>
                <button class="btn-font" onclick="diminuirFonte()" title="Diminuir fonte">−</button>
                <span class="font-percent" id="fontPercent">100%</span>
                <button class="btn-font" onclick="aumentarFonte()" title="Aumentar fonte">+</button>
            </div>
        </li>

        <!-- Toggle modo escuro -->
        <li class="nav-item me-3">
            <div class="d-flex align-items-center">
                <span class="font-label me-2" style="color:#ccc;">Modo:</span>
                <label class="switch">
                    <input type="checkbox" id="themeToggle">
                    <span class="slider round"></span>
                </label>
                <span id="themeStatus" class="ms-2 text-white" style="font-size: 12px;">Claro</span>
            </div>
        </li>

        <!-- ===== EXTREMO DIREITO: Dropdown Conta ===== -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-user"></i> Conta
          </a>
          <ul class="dropdown-menu p-0 dropdown-menu-end">
            <li><a class="btn btn-outline-danger mb-0 w-100" href="logout.php">SAIR DA CONTA</a></li>
            <li><a class="btn btn-outline-danger w-100" href="reset-password.php">ALTERAR SENHA</a></li>
          </ul>
        </li>

      </ul>
    </div>
  </div>
</nav>

<!-- Banner -->
<div class="container-fluid p-5 bg-dark text-white text-center">
  <h1>CONTATO</h1>
</div>

<div class="container mt-5 mb-5">    
  
    <div class="card-sobre-mim shadow-lg">
        
        <!-- Coluna Direita: Texto Sobre Mim -->
        <div class="col-texto">
            <h2>Contato para mais informações</h2>
            
            <p>
                Loja "Tênis dos Sonhos" levando o seu sonho até você! 
            </p>
            
            <p>
                <i class="fas fa-check-circle text-danger"></i> WhatsApp: (21) 99999-9999
                <br>
                <i class="fas fa-check-circle text-danger"></i> E-mail: contato@tenisdossonhos.com.br
                <br>
                <i class="fas fa-check-circle text-danger"></i> Horário: Segunda a Sexta, 8h às 20h
            </p>
        </div>
        
    </div>

</div>

<!-- Footer -->
<footer>
  <p>Tênis dos Sonhos<br>2026</p>
</footer>

<!-- JavaScript para alternar o tema e controlar fonte -->
<script>
    // ===== ALTERNAR TEMA =====
    document.getElementById('themeToggle').addEventListener('change', function() {
        const htmlElement = document.documentElement;
        const themeStatus = document.getElementById('themeStatus');
        
        if (this.checked) {
            htmlElement.setAttribute('data-bs-theme', 'dark');
            themeStatus.textContent = 'Escuro';
        } else {
            htmlElement.setAttribute('data-bs-theme', 'light');
            themeStatus.textContent = 'Claro';
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

    window.addEventListener('load', function() {
        const savedSize = localStorage.getItem('fontSize');
        if (savedSize) {
            fontSize = parseInt(savedSize);
            aplicarFonte();
        }
    });

    // ===== FUNÇÃO DE BUSCA PELO NOME DO PRODUTO (CARD-TITLE) =====
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let searchValue = this.value.toLowerCase();
        let cards = document.querySelectorAll('.product-card');

        cards.forEach(function(card) {
            let title = card.querySelector('.card-title');
            if (title) {
                let productName = title.innerText.toLowerCase();
                if (productName.includes(searchValue)) {
                    card.style.display = 'block'; // Mostra
                } else {
                    card.style.display = 'none';  // Esconde
                }
            }
        });
    });
</script>

</body>
</html>