<?php
session_start();
 
// Verifique se o usuário está logado e 2FA verificado
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || 
   !isset($_SESSION["2fa_verified"]) || $_SESSION["2fa_verified"] !== true){
    header("location: login.php");
    exit;
}

// BLOQUEAR usuários que NÃO são master
if(!isset($_SESSION["is_master"]) || $_SESSION["is_master"] !== true){
    header("location: main.php");
    exit;
}

// Se chegou aqui, é master com certeza
$is_master = true;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_master ? 'Painel Master' : 'Tênis dos Sonhos' ?> · Personalização Exclusiva</title>
    
    <!-- Bootstrap 5 CSS e JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Seu CSS personalizado -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* Sticky Footer */
        html, body {
            height: 100%;
        }
        
        body {
            display: flex;
            flex-direction: column;
        }
        
        .main-content {
            flex: 1 0 auto;
        }
        
        footer {
            flex-shrink: 0;
            text-align: center;
            padding: 3px;
            background-color: #212529;
            color: white;
        }
        
        [data-bs-theme="dark"] footer {
            border-top: 2px solid #dc3545 !important;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-sm navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
        <?php if($is_master): ?>
            <i class="fas fa-crown" style="color: #ffd700;"></i> Painel Master
        <?php else: ?>
            👟 Tênis dos Sonhos
        <?php endif; ?>
    </a>
    
    <!-- Badge MASTER ao lado do logo -->
    <?php if($is_master): ?>
        <span class="badge bg-warning text-dark me-2"><i class="fas fa-crown"></i> MASTER</span>
    <?php endif; ?>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="mynavbar">
      <!-- ms-auto empurra os elementos para a direita -->
      <div class="d-flex align-items-center ms-auto">
          <span class="text-white me-3">
              <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION["nome"] ?? $_SESSION["username"]) ?>
          </span>
          
          <a href="logout.php" class="btn btn-danger btn-sm">🚪 Sair</a>
      </div>
    </div>
  </div>
</nav>

<!-- Container principal (main-content) -->
<div class="main-content">
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-body">
                
                <?php if($is_master): ?>
                    <!-- ============ TELA MASTER ============ -->                    

                    <h1 class="display-5 fw-bold">Painel Master</h1>
                    <p class="fs-4 text-muted border-start border-4 border-warning ps-3">Controle Total do Sistema</p>
                    
                    <p class="lead">Bem-vindo ao painel de administração, <strong><?= htmlspecialchars($_SESSION["nome"] ?? $_SESSION["username"]) ?></strong>. 
                    Aqui você tem acesso a todas as funcionalidades de gerenciamento.</p>
                    
                    <!-- Grid de Cards Master -->
                    <div class="row g-4 mt-3">
                        <div class="col-md-6">
                            <a href="logs.php" class="text-decoration-none">
                                <div class="card h-100 bg-primary text-white">
                                    <div class="card-body">
                                        <div class="fs-1 mb-2"><i class="fas fa-history"></i></div>
                                        <h5 class="card-title fw-bold">Logs do Sistema</h5>
                                        <p class="card-text opacity-75">Login e logout</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="usuarios.php" class="text-decoration-none">
                                <div class="card h-100 bg-info text-white">
                                    <div class="card-body">
                                        <div class="fs-1 mb-2"><i class="fas fa-users"></i></div>
                                        <h5 class="card-title fw-bold">Usuários</h5>
                                        <p class="card-text opacity-75">Gerenciar cadastros</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                <?php else: ?>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- Footer -->
<footer>
  <p>Tênis dos Sonhos<br>2026</p>
</footer>

</body>
</html>