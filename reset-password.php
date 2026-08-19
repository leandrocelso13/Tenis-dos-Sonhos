<?php
// ==========================================
// SISTEMA DE PROTEÇÃO CONTRA ERROS FATAIS
// ==========================================
function lidarComErroFatal($exception) {
    if (ob_get_length()) ob_clean();
    $mensagem = urlencode("Erro interno do sistema.");
    header("location: erro.php?msg=" . $mensagem);
    exit();
}
set_exception_handler('lidarComErroFatal');
ob_start();
// ==========================================

// Inicialize a sessão
session_start();
 
// Verifique se o usuário está logado, caso contrário, redirecione para a página de login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
 
// Incluir arquivo de configuração
require_once "config.php";
 
// Defina variáveis e inicialize com valores vazios
$current_password = $new_password = $confirm_password = "";
$current_password_err = $new_password_err = $confirm_password_err = "";
 
// Processando dados do formulário quando o formulário é enviado
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validar senha atual
    if(empty(trim($_POST["current_password"]))){
        $current_password_err = "Por favor insira a senha atual.";
    } else{
        $current_password = trim($_POST["current_password"]);
        
        // Verificar se a senha atual está correta
        // CORREÇÃO: Tabela 'cadastro' e coluna 'senha'
        $sql = "SELECT senha FROM cadastro WHERE id = :id";
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":id", $_SESSION["id"], PDO::PARAM_INT);
            if($stmt->execute()){
                if($row = $stmt->fetch()){
                    // CORREÇÃO AQUI: Usar $row["senha"] (e não password)
                    if(!password_verify($current_password, $row["senha"])){
                        $current_password_err = "A senha atual está incorreta.";
                    }
                }
            }
            unset($stmt);
        }
    }
 
    // Validar nova senha
    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Por favor insira a nova senha.";     
    } elseif(strlen(trim($_POST["new_password"])) > 8){
        $new_password_err = "A senha deve ter no máximo 8 caracteres.";
    } else{
        $new_password = trim($_POST["new_password"]);
    }
    
    // Validar e confirmar a senha
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Por favor, confirme a senha.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "A senha não confere.";
        }
    }
        
    // Verifique os erros de entrada antes de atualizar o banco de dados
    if(empty($current_password_err) && empty($new_password_err) && empty($confirm_password_err)){
        // Prepare uma declaração de atualização
        // CORREÇÃO: Tabela 'cadastro' e coluna 'senha'
        $sql = "UPDATE cadastro SET senha = :senha WHERE id = :id";
        
        if($stmt = $pdo->prepare($sql)){
            // Vincule as variáveis à instrução preparada como parâmetros
            $stmt->bindParam(":senha", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":id", $param_id, PDO::PARAM_INT);
            
            // Definir parâmetros
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];
            
            // Tente executar a declaração preparada
            if($stmt->execute()){
                // Senha atualizada com sucesso. Destrua a sessão e redirecione para a página de login
                session_destroy();
                header("location: login.php");
                exit();
            } else{
                echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            // Fechar declaração
            unset($stmt);
        }
    }
    
    // Fechar conexão
    unset($pdo);
}
?>
 
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar senha</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font: 14px sans-serif;
            /* Fundo escuro */
            background-color: #000000;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.02) 0%, transparent 70%);
            min-height: 100vh;
            height: 100vh;
            width: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .wrapper {
            width: 360px;
            padding: 30px;
            background-color: rgb(0, 0, 0);
            border-radius: 10px;
            box-shadow: 
                0 0 10px #ff0000,
                0 0 20px #ff0000,
                0 0 30px #ff0000,
                0 0 40px #ff0000,
                0 0 70px #ff0000,
                0 0 100px #ff0000,
                0 0 20px rgba(0, 0, 0, 0.5);
            /* Borda vermelha neon */
            border: 2px solid #ff0000;
            /* Animação do efeito neon */
            animation: neonPulse 1.5s ease-in-out infinite alternate;
        }
        
        /* Animação para o efeito neon pulsar */
        @keyframes neonPulse {
            from {
                box-shadow: 
                    0 0 10px #ff0000,
                    0 0 20px #ff0000,
                    0 0 30px #ff0000,
                    0 0 40px #ff0000,
                    0 0 70px #ff0000,
                    0 0 100px #ff0000,
                    0 0 20px rgba(0, 0, 0, 0.5);
            }
            to {
                box-shadow: 
                    0 0 5px #ff0000,
                    0 0 10px #ff0000,
                    0 0 20px #ff0000,
                    0 0 30px #ff0000,
                    0 0 50px #ff0000,
                    0 0 80px #ff0000,
                    0 0 20px rgba(0, 0, 0, 0.5);
            }
        }
        
        h2 {
            color: #ffffff;
            margin-bottom: 20px;
        }
        
        p {
            color: #ffffff;
        }
        
        .form-group label {
            font-weight: 600;
            color: #ffffff;
        }
        
        /* Estilo dos campos do formulário - borda vermelha FORTE */
        .form-control {
            border: 2px solid #ff0000;
            background-color: #1a1a1a;
            color: #ffffff;
        }
        
        .form-control:focus {
            border-color: #ff0000;
            box-shadow: none;
            background-color: #1a1a1a;
            color: #ffffff;
        }
        
        .form-control::placeholder {
            color: #888888;
        }
        
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 8px 20px;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        
        /* Ajustar botões para o mesmo tamanho */
        .btn-group-custom {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .btn-custom {
            flex: 1;
            max-width: 150px;
        }
        
        /* ALTERAÇÃO AQUI - Cor do texto dos botões para branco */
        .btn-outline-danger {
            color: #ffffff !important;
            border-color: #ff0000;
        }
        
        .btn-outline-danger:hover {
            background-color: #ff0000;
            color: #ffffff !important;
            border-color: #ff0000;
        }
        
        .btn-outline-danger:focus {
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    
    <div class="wrapper text-center">
        <h2>Alterar senha</h2>
        <p>Por favor, preencha este formulário para alterar sua senha.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"> 
            <div class="form-group">
                <label>Senha atual</label>
                <!-- maxlength="8" em todos os campos de senha -->
                <input type="password" name="current_password" class="form-control <?php echo (!empty($current_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $current_password; ?>" maxlength="8">
                <span class="invalid-feedback"><?php echo $current_password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Nova senha</label>
                <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>" maxlength="8">
                <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirme a senha</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" maxlength="8">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group btn-group-custom">
                <input type="submit" class="btn btn-outline-danger btn-custom" value="Alterar">
                <a class="btn btn-outline-danger btn-custom" href="main.php">Cancelar</a>
            </div>
        </form>
    </div>    
</body>
</html>