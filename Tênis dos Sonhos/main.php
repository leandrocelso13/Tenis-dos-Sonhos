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
    
    <!-- Seu CSS personalizado -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        /*Preenche espaço*/
        * { margin: 0; padding: 0; box-sizing: border-box; }
         
        /* Carrossel */
        .carousel-container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .carousel { border-radius: 20px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.2); }
        .carousel-item img { width: 100%; height: 500px; object-fit: cover; }
        .carousel-control-prev, .carousel-control-next {
            width: 50px; height: 50px; background: rgba(0,0,0,0.5);
            border-radius: 50%; top: 50%; transform: translateY(-50%); margin: 0 20px;
        }
        .carousel-control-prev:hover, .carousel-control-next:hover { background: rgba(0,0,0,0.8); }
        .carousel-indicators button { width: 12px; height: 12px; border-radius: 50%; margin: 0 5px; }
        .carousel-caption { background: rgba(0,0,0,0.6); border-radius: 10px; padding: 15px; bottom: 30px; }
        .carousel-caption h3 { font-size: 1.8rem; font-weight: 600; margin-bottom: 10px; }
        .carousel-caption p { font-size: 1.1rem; margin-bottom: 0; color: #fff; }      

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
    </style>
</head>
<body>

<!-- Navbar SEM borda no modo claro -->
<nav class="navbar navbar-expand-sm navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">        
       👟 Tênis dos Sonhos       
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
      <span class="navbar-toggler-icon"></span>
    </button>   
    
    <div class="collapse navbar-collapse" id="mynavbar">
      <!-- Links de navegação à esquerda -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="#">Início</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Menu</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Produtos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Sobre</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Contato</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-user"></i> Conta
          </a>
          <ul class="dropdown-menu p-0">
            <li><a class="btn btn-outline-danger mb-0 w-100" href="logout.php">SAIR DA CONTA</a></li>
            <li><a class="btn btn-outline-danger w-100" href="reset-password.php">ALTERAR SENHA</a></li>
          </ul>
        </li>
      </ul>
      
      <!-- ===== CONTROLES DE FONTE E TEMA - Mais à direita ===== -->
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
        <li class="nav-item me-2">
            <div class="d-flex align-items-center">
                <span class="font-label me-2" style="color:#ccc;">Modo:</span>
                <label class="switch">
                    <input type="checkbox" id="themeToggle">
                    <span class="slider round"></span>
                </label>
                <span id="themeStatus" class="ms-2 text-white" style="font-size: 12px;">Claro</span>
            </div>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Carrossel -->
<div class="carousel-container">
    <div id="demo" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#demo" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#demo" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#demo" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="imagens/mario.jpg" alt="COMPRE SEU TÊNIS AGORA" class="d-block w-100">
                <div class="carousel-caption d-none d-md-block">
                    <h3>✨ COMPRE SEU TÊNIS AGORA ✨</h3>
                    <p>Modelos exclusivos para você</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="imagens/t7.jpg" alt="BEM VINDO" class="d-block w-100">
                <div class="carousel-caption d-none d-md-block">
                    <h3>👋 BEM-VINDO 👋</h3>
                    <p>Sua jornada de estilo começa aqui</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="imagens/t4.jpg" alt="PERSONALIZE SEU TÊNIS" class="d-block w-100">
                <div class="carousel-caption d-none d-md-block">
                    <h3>🎨 TÊNIS PERSONALIZADO 🎨</h3>
                    <p>O tênis dos seus sonhos</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#demo" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#demo" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Próximo</span>
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myCarousel = document.querySelector('#demo');
        if (myCarousel) {
            var carousel = new bootstrap.Carousel(myCarousel, {
                interval: 3000,
                ride: 'carousel',
                pause: 'hover'
            });
        }
    });
</script>

<!-- BS5 HOME SEM borda no modo claro -->
<div class="container-fluid p-5 bg-dark text-white text-center">
  <h1>Tênis dos Sonhos</h1>
  <p>Compre tênis barato e bonito</p> 
</div>
  
<div class="container mt-5">    
  <div class="row">
    <div class="col-sm-4 text-center">      
      <div class="card border-danger border-2" style="width:100%">
        <img class="card-img-top" src="./imagens/t1.png" alt="Card image" style="width:100%; height: 300px; object-fit: cover;">
        <div class="card-body text-center">
          <h4 class="card-title">NIKE EDIÇÃO LIMITADA</h4>
          <p class="card-text">Sua melhor escolha começa aqui <h2>R$ 500,00 reais</h2>
          <p>PROMOÇÃO!</p></p></p>
          <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-outline-danger btn-lg">COMPRAR</button>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-4 text-center"> 
      <div class="card border-danger border-2" style="width:100%">
        <img class="card-img-top" src="./imagens/t2.jpg" alt="Card image" style="width:100%; height: 300px; object-fit: cover;">
        <div class="card-body text-center">
          <h4 class="card-title">FOGO E GELO</h4>
          <p class="card-text">Sua melhor escolha começa aqui<h2>R$ 150,00 reais</h2>
          <p>PROMOÇÃO!</p></p></p>
          <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-outline-danger btn-lg">COMPRAR</button>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-4 text-center">
      <div class="card border-danger border-2" style="width:100%">
        <img class="card-img-top" src="./imagens/t3.jpg" alt="Card image" style="width:100%; height: 300px; object-fit: cover;">
        <div class="card-body text-center">
          <h4 class="card-title">SUPER NIKE</h4>
          <p class="card-text">Sua melhor escolha começa aqui<h2>R$ 250,00 reais</h2>
          <p>PROMOÇÃO!</p></p>
          <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-outline-danger btn-lg">COMPRAR</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container mt-5">    
  <div class="row">
    <div class="col-sm-4 text-center">      
      <div class="card border-danger border-2" style="width:100%">
        <img class="card-img-top" src="./imagens/t4.jpg" alt="Card image" style="width:100%; height: 300px; object-fit: cover;">
        <div class="card-body text-center">
          <h4 class="card-title">TATUAGEM DE BORBOLETA</h4>
          <p class="card-text">Sua melhor escolha começa aqui<h2>R$ 300,00 reais</h2>
          <p>PROMOÇÃO!</p></p></p>
          <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-outline-danger btn-lg">COMPRAR</button>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-4 text-center"> 
      <div class="card border-danger border-2" style="width:100%">
        <img class="card-img-top" src="./imagens/t5.jpg" alt="Card image" style="width:100%; height: 300px; object-fit: cover;">
        <div class="card-body text-center">
          <h4 class="card-title">SONIC</h4>
          <p class="card-text">Sua melhor escolha começa aqui<h2>R$ 450,00 reais</h2>
          <p>PROMOÇÃO!</p></p></p>
          <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-outline-danger btn-lg">COMPRAR</button>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-4 text-center">
      <div class="card border-danger border-2" style="width:100%">
        <img class="card-img-top" src="./imagens/t6.jpg" alt="Card image" style="width:100%; height: 300px; object-fit: cover;">
        <div class="card-body text-center">
          <h4 class="card-title">23</h4>
          <p class="card-text">Sua melhor escolha começa aqui<h2>R$ 100,00 reais</h2>
          <p>PROMOÇÃO!</p></p>
          <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-outline-danger btn-lg">COMPRAR</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container mt-5 mb-5">    
  <div class="row">
    <div class="col-sm-4 text-center">      
      <div class="card border-danger border-2" style="width:100%">
        <img class="card-img-top" src="./imagens/t7.jpg" alt="Card image" style="width:100%; height: 300px; object-fit: cover;">
        <div class="card-body text-center">
          <h4 class="card-title">JOKER</h4>
          <p class="card-text">Sua melhor escolha começa aqui<h2>R$ 500,00 reais</h2>
          <p>PROMOÇÃO!</p></p></p>
          <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-outline-danger btn-lg">COMPRAR</button>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-4 text-center"> 
      <div class="card border-danger border-2" style="width:100%">
        <img class="card-img-top" src="./imagens/t8.jpg" alt="Card image" style="width:100%; height: 300px; object-fit: cover;">
        <div class="card-body text-center">
          <h4 class="card-title">LIGHT YEAR</h4>
          <p class="card-text">Sua melhor escolha começa aqui<h2>R$ 150,00 reais</h2>
          <p>PROMOÇÃO!</p></p></p>
          <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-outline-danger btn-lg">COMPRAR</button>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-4 text-center">
      <div class="card border-danger border-2" style="width:100%">
        <img class="card-img-top" src="./imagens/t9.jpg" alt="Card image" style="width:100%; height: 300px; object-fit: cover;">
        <div class="card-body text-center">
          <h4 class="card-title">BOB ESPONJA</h4>
          <p class="card-text">Sua melhor escolha começa aqui<h2>R$ 250,00 reais</h2>
          <p>PROMOÇÃO!</p></p>
          <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-outline-danger btn-lg">COMPRAR</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Footer SEM borda no modo claro -->
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
    let fontSize = 100; // percentual base

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
        // Aplica o tamanho da fonte no elemento HTML (raiz do documento)
        document.documentElement.style.fontSize = fontSize + '%';
        document.getElementById('fontPercent').textContent = fontSize + '%';
        // Salva no localStorage para manter entre recarregamentos
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