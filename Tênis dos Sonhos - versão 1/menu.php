<?php
// Inicialize a sessão
session_start();
 
// Verifique se o usuário está logado, se não, redirecione-o para uma página de login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>
 
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tênis dos Sonhos · Personalização Exclusiva</title>
    
    <!-- Bootstrap (mantido conforme original) 
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Seu CSS personalizado (mantido) -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* Reset e estilo base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(145deg, #f9f7f5 0%, #edece8 100%);
            font-family: 'Segoe UI', Roboto, system-ui, -apple-system, sans-serif;
            color: #1e1e1e;
            text-align: left; /* Sobrescreve o text-align: center do style original */
        }

        /* Header mantido com a imagem */
        header {
            background: url('imagens/header.jpg') center/cover no-repeat;
            height: 300px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        /* Container principal com o card profissional */
        .main-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
        }

        .card-profissional {
            max-width: 900px;
            width: 100%;
            background: #ffffff;
            border-radius: 36px;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.18), 0 8px 24px -8px rgba(0, 0, 0, 0.1);
            padding: 2.5rem 2.8rem;
            margin: -50px auto 2rem auto; /* Sobreposição sutil sobre o header */
            border: 1px solid rgba(220, 210, 200, 0.3);
        }

        /* Boas-vindas do usuário */
        .welcome-box {
            background: #f3efe9;
            padding: 0.8rem 1.8rem;
            border-radius: 60px;
            display: inline-block;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            color: #2c3e4e;
            border: 1px solid #d4c9bc;
        }

        .welcome-box b {
            color: #1f2a36;
            font-weight: 700;
        }

        /* Título principal */
        h1.titulo-principal {
            font-size: 3.2rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.1;
            color: #1f2a36;
            margin-bottom: 0.5rem;
            word-break: break-word;
        }

        /* SUBTÍTULO - Exatamente abaixo do título conforme solicitado */
        .subtitulo-exclusivo {
            font-size: 1.7rem;
            font-weight: 400;
            color: #5a4a3e;
            border-left: 5px solid #b47c5e;
            padding-left: 1.4rem;
            margin-top: 0.2rem;
            margin-bottom: 2.2rem;
            font-style: normal;
            letter-spacing: -0.01em;
        }

        /* Parágrafos */
        p {
            font-size: 1.15rem;
            line-height: 1.6;
            color: #2c353d;
            margin-bottom: 1.6rem;
            font-weight: 400;
        }

        .destaque {
            font-weight: 600;
            color: #1f2a36;
        }

        .custom-badge {
            background: #efe6dd;
            color: #3e2f25;
            padding: 0.2rem 0.7rem;
            border-radius: 30px;
            font-size: 0.95rem;
            font-weight: 500;
            display: inline-block;
            margin-left: 0.3rem;
        }

        /* Bloco "Como Funciona" */
        .steps-container {
            margin: 2.2rem 0 2rem 0;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 1.4rem;
            margin-bottom: 1.8rem;
        }

        .step-number {
            background: #1f2a36;
            color: white;
            width: 46px;
            height: 46px;
            border-radius: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.4rem;
            flex-shrink: 0;
            box-shadow: 0 6px 12px rgba(0,0,0,0.06);
        }

        .step-text {
            font-size: 1.15rem;
            line-height: 1.5;
            padding-top: 6px;
        }

        .step-text strong {
            color: #1f2a36;
            font-weight: 700;
        }

        /* Área de botões de conta (Redefinir senha / Logout) */
        .account-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-start;
            margin: 1.5rem 0 0.5rem 0;
            flex-wrap: wrap;
        }

        .account-actions .btn {
            border-radius: 40px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            border: none;
            transition: all 0.2s;
        }

        .btn-warning {
            background: #e0c9b6;
            color: #2e241e;
            border: 1px solid #c8aa92 !important;
        }

        .btn-warning:hover {
            background: #d2b59e;
            color: #1a1410;
        }

        .btn-danger {
            background: #d88c7a;
            color: #fff;
            border: 1px solid #b96b58 !important;
        }

        .btn-danger:hover {
            background: #c07867;
        }

        /* CTA WhatsApp */
        .cta-box {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 2px dashed #d9cdc2;
        }

        .frase-final {
            font-size: 1.6rem;
            font-weight: 600;
            margin: 1.5rem 0 1.2rem 0;
            color: #1f2a36;
        }

        .whatsapp-link {
            display: inline-block;
            background: #25D366;
            color: #0a1f11;
            padding: 0.9rem 2.2rem;
            border-radius: 60px;
            font-weight: 600;
            font-size: 1.25rem;
            text-decoration: none;
            transition: 0.15s;
            box-shadow: 0 6px 14px rgba(37, 211, 102, 0.25);
            border: 1px solid #1da15b;
            margin-top: 0.5rem;
        }

        .whatsapp-link:hover {
            background: #1da15b;
            color: white;
            box-shadow: 0 10px 18px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
            text-decoration: none;
        }

        .whatsapp-link i {
            margin-right: 8px;
            font-style: normal;
            font-size: 1.5rem;
        }

        /* Footer mantido com a imagem */
        footer {
            background: url('imagens/footer.jpg') center/cover no-repeat;
            height: 300px;
            width: 100%;
            margin-top: 1rem;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
        }

        /* Ajustes responsivos */
        @media (max-width: 700px) {
            .card-profissional { 
                padding: 2rem 1.5rem; 
                border-radius: 28px;
                margin-top: -30px;
            }
            h1.titulo-principal { font-size: 2.4rem; }
            .subtitulo-exclusivo { font-size: 1.4rem; padding-left: 1rem; }
            p, .step-text { font-size: 1.05rem; }
            .step-number { width: 40px; height: 40px; font-size: 1.2rem; }
            .welcome-box { font-size: 1rem; }
        }

        /* Compatibilidade com Bootstrap */
        .btn {
            box-shadow: none !important;
        }
        
        /* Ajuste para o texto não ficar centralizado devido ao style inline original */
        .text-left-important {
            text-align: left !important;
        }
    </style>
</head>
<body>
    <!-- Header com imagem (mantido exatamente como no original) 
    <header></header>
-->

<nav class="navbar navbar-expand-sm navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="javascript:void(0)">Logo</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mynavbar">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="home.php">Voltar</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="javascript:void(0)">Link</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="javascript:void(0)">Link</a>
        </li>
      </ul>

    <ul class="nav navbar-nav navbar-right">
            <li><a href="#"><span>            <div class="account-actions">
                <a href="reset-password.php" class="btn btn-warning">🔐 Redefinir senha</a>
                <a href="logout.php" class="btn btn-danger">🚪 Sair da conta</a>
            </div>
            </span> Login</a></li> 
    </ul>
    </div>
  </div>
</nav>


    <!-- Container principal com o card profissional -->
    <div class="main-wrapper">
        <div class="card-profissional text-left-important">
            
            <!-- Saudação ao usuário logado -->
            <div class="welcome-box">
                👋 Oi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Seu ateliê de criação está pronto.
            </div>
            
            <!-- TÍTULO PRINCIPAL -->
            <h1 class="titulo-principal">Página 2</h1>
            


            
            <!-- As frases originais ficam integradas no design, mas se quiser manter o texto simples, ele está incorporado no parágrafo inicial e no passo 3 -->
        </div>
    </div>

    <!-- Footer com imagem (mantido exatamente como no original) -->
    <footer></footer>

    <!-- Scripts opcionais do Bootstrap (mantidos) 
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>-->
</body>
</html>