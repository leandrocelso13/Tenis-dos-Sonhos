<?php
session_start();

// ========== VERIFICAÇÃO DE ACESSO AO login.php ==========
// Primeiro verifica se já está logado (se estiver, redireciona para a página correta)
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && 
   isset($_SESSION["2fa_verified"]) && $_SESSION["2fa_verified"] === true){
    if(isset($_SESSION["is_master"]) && $_SESSION["is_master"] === true){
        header("location: master.php");
    } else {
        header("location: main.php");
    }
    exit;
}

// Se NÃO estiver logado, verifica de onde veio
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Pega o referer (de onde o usuário veio)
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    
    // Páginas permitidas para acessar o login.php diretamente
    $allowed_pages = ['home.php', 'cadastro.php'];
    $allowed_from_page = false;
    
    foreach($allowed_pages as $page) {
        if(strpos($referer, $page) !== false) {
            $allowed_from_page = true;
            break;
        }
    }
    
    // Verifica se está no processo de 2FA (pode ter vindo de um POST do login)
    $is_2fa_process = isset($_SESSION["temp_loggedin"]) && $_SESSION["temp_loggedin"] === true;
    $is_2fa_submit = isset($_POST['2fa_submit']);
    
    // Verifica se está enviando o formulário de login (POST)
    $is_login_submit = ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['2fa_submit']));
    
    // Se não veio de uma página permitida, não está no processo 2FA e não está enviando o formulário, bloqueia
    if(!$allowed_from_page && !$is_2fa_process && !$is_2fa_submit && !$is_login_submit) {
        header("location: home.php");
        exit;
    }
}

require_once "config.php";
require_once "funcoes.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";
$show_2fa = false;

// Processar formulário de login
if($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['2fa_submit'])){
    if(empty(trim($_POST["login"]))){
        $username_err = "Por favor, insira o nome de usuário.";
    } else{
        $username = trim($_POST["login"]);
    }
    
    if(empty(trim($_POST["senha"]))){
        $password_err = "Por favor, insira sua senha.";
    } else{
        $password = trim($_POST["senha"]);
        
        // Validação: Se a senha tiver mais de 8 caracteres
        if(strlen($password) > 8){
            $password_err = "A senha deve ter no máximo 8 caracteres.";
        }
    }
    
    if(empty($username_err) && empty($password_err)){
        // O seu SELECT já estava certo! Ele pega nome_completo e data_de_nascimento
        $sql = "SELECT id, login, nome_completo, senha, nome_materno, data_de_nascimento, cep 
                FROM cadastro 
                WHERE login = :login";
        
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":login", $param_login, PDO::PARAM_STR);
            $param_login = trim($_POST["login"]);
            
            try {
                if($stmt->execute() && $stmt->rowCount() == 1){
                    if($row = $stmt->fetch()){
                        if(password_verify($password, $row["senha"])){
                            // Login bem-sucedido, preparar 2FA
                            $_SESSION["temp_id"] = $row["id"];
                            $_SESSION["temp_username"] = $row["login"];
                            
                            // Armazena o nome completo vindo do banco
                            $_SESSION["temp_nome"] = $row["nome_completo"];
                            
                            $_SESSION["temp_loggedin"] = true;
                            
                            // ================================================
                            // REMOVIDO: O LOG DE 'login' ANTES DO 2FA
                            // ================================================
                            
                            // Formatar a data correta do banco (data_de_nascimento) para o 2FA
                            $data_nascimento = $row["data_de_nascimento"] ?? '';
                            if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_nascimento)){
                                $date_parts = explode('-', $data_nascimento);
                                $data_nascimento = $date_parts[2] . '/' . $date_parts[1] . '/' . $date_parts[0];
                            }
                            
                            // Formatar CEP - Remove tudo que não for número
                            $cep_original = $row["cep"] ?? '';
                            $cep_limpo = preg_replace('/[^0-9]/', '', $cep_original);
                            
                            // Armazenar os dados para verificação 2FA
                            $_SESSION["2fa_data"] = [
                                'nome_mae' => $row["nome_materno"] ?? '',
                                'data_nascimento' => $data_nascimento,
                                'cep' => $cep_limpo
                            ];
                            
                            $_SESSION["2fa_attempts"] = 3;
                            
                            // GERAR PERGUNTA ALEATÓRIA
                            $questions = [
                                [
                                    'texto' => 'Qual o nome da sua mãe?',
                                    'campo' => 'nome_mae',
                                    'icone' => 'fa-user',
                                    'placeholder' => 'Digite o nome completo da sua mãe',
                                    'tipo' => 'text'
                                ],
                                [
                                    'texto' => 'Qual a data do seu nascimento?',
                                    'campo' => 'data_nascimento',
                                    'icone' => 'fa-calendar',
                                    'placeholder' => 'Selecione a data',
                                    'tipo' => 'date'
                                ],
                                [
                                    'texto' => 'Qual o CEP do seu endereço?',
                                    'campo' => 'cep',
                                    'icone' => 'fa-map-marker-alt',
                                    'placeholder' => 'Digite apenas os 8 números do CEP',
                                    'tipo' => 'text'
                                ]
                            ];
                            
                            $random_index = array_rand($questions);
                            $_SESSION['2fa_question_field'] = $questions[$random_index]['campo'];
                            $_SESSION['2fa_selected_question'] = $questions[$random_index];
                            
                            $show_2fa = true;
                        } else {
                            $login_err = "Nome de usuário ou senha inválidos.";
                        }
                    }
                } else {
                    $login_err = "Nome de usuário ou senha inválidos.";
                }
            } catch(PDOException $e) {
                // ERRO INESPERADO - Redireciona para a tela de erro
                header("location: erro.php?msg=" . urlencode(""));
                exit;
            }
            unset($stmt);
        } else {
            // ERRO INESPERADO - Redireciona para a tela de erro
            header("location: erro.php?msg=" . urlencode(""));
            exit;
        }
    }
    unset($pdo);
}

// Processar verificação 2FA
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['2fa_submit'])){
    $show_2fa = true;
    
    $user_answer = trim($_POST['2fa_answer'] ?? '');
    
    if(empty($user_answer)){
        $login_err = "Por favor, preencha a resposta.";
    } else {
        $question_field = $_SESSION['2fa_question_field'] ?? '';
        $correct_answer = $_SESSION["2fa_data"][$question_field] ?? '';
        
        $user_answer_normalized = strtolower(trim($user_answer));
        $correct_answer_normalized = strtolower(trim($correct_answer));
        
        switch($question_field) {
            case 'nome_mae':
                $user_answer_normalized = removerAcentos($user_answer_normalized);
                $correct_answer_normalized = removerAcentos($correct_answer_normalized);
                $user_answer_normalized = preg_replace('/\s+/', ' ', $user_answer_normalized);
                $correct_answer_normalized = preg_replace('/\s+/', ' ', $correct_answer_normalized);
                break;
                
            case 'data_nascimento':
                if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $user_answer)){
                    $date_parts = explode('-', $user_answer);
                    $user_answer_normalized = $date_parts[2] . '/' . $date_parts[1] . '/' . $date_parts[0];
                }
                elseif(preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $user_answer)){
                    $user_answer_normalized = $user_answer;
                }
                elseif(preg_match('/^\d{8}$/', $user_answer)){
                    $user_answer_normalized = substr($user_answer, 0, 2) . '/' . 
                                             substr($user_answer, 2, 2) . '/' . 
                                             substr($user_answer, 4, 4);
                }
                
                if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $correct_answer)){
                    $date_parts = explode('-', $correct_answer);
                    $correct_answer_normalized = $date_parts[2] . '/' . $date_parts[1] . '/' . $date_parts[0];
                }
                elseif(preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $correct_answer)){
                    $correct_answer_normalized = $correct_answer;
                }
                
                $user_answer_normalized = preg_replace('/\b0(\d)\b/', '$1', $user_answer_normalized);
                $correct_answer_normalized = preg_replace('/\b0(\d)\b/', '$1', $correct_answer_normalized);
                break;
                
            case 'cep':
                $user_answer_normalized = preg_replace('/[^0-9]/', '', $user_answer_normalized);
                $correct_answer_normalized = preg_replace('/[^0-9]/', '', $correct_answer_normalized);
                $user_answer_normalized = substr($user_answer_normalized, 0, 8);
                $correct_answer_normalized = substr($correct_answer_normalized, 0, 8);
                break;
        }
        
        if($user_answer_normalized === $correct_answer_normalized){
            // ================================================
            // CORRIGIDO: AGORA É UM ÚNICO LOG COM AÇÃO 'login'
            // E O TIPO DE 2FA É PASSADO JUNTO
            // ================================================
            try {
                if(function_exists('registrarLog')){
                    // Obtém o tipo de 2FA utilizado
                    $tipo_2fa = $_SESSION['2fa_question_field'] ?? '';
                    $tipo_2fa_map = [
                        'nome_mae' => 'nome_materno',
                        'data_nascimento' => 'data_de_nascimento',
                        'cep' => 'cep'
                    ];
                    $tipo_2fa_log = $tipo_2fa_map[$tipo_2fa] ?? null;
                    
                    // Registra o log com a ação 'login' e o tipo de 2FA
                    registrarLog($pdo, $_SESSION["temp_username"], 'login', $tipo_2fa_log);
                }
            } catch(Exception $e) {
                // Ignora erro de log para não quebrar o login
            }
            // ================================================
            
            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $_SESSION["temp_id"];
            $_SESSION["username"] = $_SESSION["temp_username"];
            $_SESSION["nome"] = $_SESSION["temp_nome"];
            $_SESSION["2fa_verified"] = true;
            
            $_SESSION["is_master"] = isMaster($_SESSION["username"]);
            
            unset($_SESSION["temp_id"]);
            unset($_SESSION["temp_username"]);
            unset($_SESSION["temp_nome"]);
            unset($_SESSION["temp_loggedin"]);
            unset($_SESSION["2fa_data"]);
            unset($_SESSION["2fa_attempts"]);
            unset($_SESSION["2fa_question_field"]);
            unset($_SESSION["2fa_selected_question"]);
            
            if($_SESSION["is_master"]){
                header("location: master.php");
            } else {
                header("location: main.php");
            }
            exit;
        } else {
            $_SESSION["2fa_attempts"] = ($_SESSION["2fa_attempts"] ?? 3) - 1;
            
            if($_SESSION["2fa_attempts"] <= 0){
                session_destroy();
                header("location: login.php?blocked=1");
                exit;
            } else {
                $login_err = "Resposta incorreta! " . $_SESSION["2fa_attempts"] . " tentativa(s) restante(s).";
            }
        }
    }
}

function removerAcentos($string) {
    $comAcentos = array('à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ü', 'Ú');
    $semAcentos = array('a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U');
    return str_replace($comAcentos, $semAcentos, $string);
}

$blocked_message = "";
if(isset($_GET['blocked']) && $_GET['blocked'] == 1){
    $blocked_message = " ";
}

$current_question = $_SESSION['2fa_selected_question'] ?? null;
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Login - Verificação 2FA</title>
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
            width: 100%;
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            transition: background-color 0.3s ease, color 0.3s ease;
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

        /* ===== MODO ESCURO ===== */
        [data-bs-theme="dark"] body {
            background-color: #1a1a1a !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .wrapper {
            background-color: #2d2d2d !important;
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .wrapper-blocked {
            background-color: #2d2d2d !important;
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] h2, 
        [data-bs-theme="dark"] h3, 
        [data-bs-theme="dark"] h4,
        [data-bs-theme="dark"] p,
        [data-bs-theme="dark"] label,
        [data-bs-theme="dark"] .font-size-display,
        [data-bs-theme="dark"] .page-indicator,
        [data-bs-theme="dark"] .question-text,
        [data-bs-theme="dark"] .security-badge h3,
        [data-bs-theme="dark"] .security-badge p,
        [data-bs-theme="dark"] .attempts-badge,
        [data-bs-theme="dark"] .blocked-message p,
        [data-bs-theme="dark"] a,
        [data-bs-theme="dark"] .text-center,
        [data-bs-theme="dark"] .question-box small,
        [data-bs-theme="dark"] .controls-container .font-size-display {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .wrapper-blocked h4,
        [data-bs-theme="dark"] .wrapper-blocked h5,
        [data-bs-theme="dark"] .wrapper-blocked p {
            color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .wrapper-blocked {
            background: #2d2d2d !important;
            border-color: #dc3545 !important;
            box-shadow: 0 0 30px rgba(220, 53, 69, 0.3) !important;
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

        [data-bs-theme="dark"] .form-control {
            background-color: #3d3d3d !important;
            color: #ffffff !important;
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .form-control:focus {
            background-color: #3d3d3d !important;
            color: #ffffff !important;
            border-color: #dc3545 !important;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.3) !important;
        }

        [data-bs-theme="dark"] .form-control::placeholder {
            color: #bbbbbb !important;
        }

        [data-bs-theme="dark"] .form-control-lg {
            background-color: #3d3d3d !important;
            color: #ffffff !important;
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .form-control-lg:focus {
            background-color: #3d3d3d !important;
            color: #ffffff !important;
            border-color: #dc3545 !important;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.3) !important;
        }

        [data-bs-theme="dark"] .form-control-lg::placeholder {
            color: #bbbbbb !important;
        }

        [data-bs-theme="dark"] .question-box {
            background: #2d2d2d !important;
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .question-box .question-text {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .question-box small {
            color: #cccccc !important;
        }

        [data-bs-theme="dark"] .attempts-badge {
            background: #2d2d2d !important;
            border-color: #dc3545 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .attempts-badge .remaining {
            color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .attempts-badge .danger {
            color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .alert-danger {
            background-color: #3d1a1a !important;
            color: #ff6666 !important;
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .page-indicator {
            color: #cccccc !important;
        }

        [data-bs-theme="dark"] .security-badge h3 {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .security-badge p {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .text-center {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] a {
            color: #ff6b6b !important;
        }

        [data-bs-theme="dark"] a:hover {
            color: #ff4444 !important;
        }

        [data-bs-theme="dark"] .btn-outline-danger {
            color: #ffffff !important;
            border-color: #dc3545 !important;
        }

        [data-bs-theme="dark"] .btn-outline-danger:hover {
            background-color: #dc3545 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .invalid-feedback {
            color: #ff6b6b !important;
        }

        [data-bs-theme="dark"] #themeStatus {
            color: #ffffff !important;
        }

        /* ===== WRAPPER (JANELA DE LOGIN) ===== */
        .wrapper { 
            width: 480px; 
            padding: 30px; 
            background-color: #ffffff;
            border-radius: 10px;
            border: 2px solid #dc3545;
            box-shadow: 0 0 30px rgba(220, 53, 69, 0.3);
            transition: all 0.3s ease;
        }

        /* ===== WRAPPER PARA MENSAGEM DE BLOQUEIO ===== */
        .wrapper-blocked { 
            width: 480px; 
            padding: 40px; 
            background-color: #ffffff;
            border-radius: 10px;
            border: 2px solid #dc3545;
            box-shadow: 0 0 30px rgba(220, 53, 69, 0.3);
            text-align: center;
            transition: all 0.3s ease;
        }

        .wrapper-blocked .warning-icon {
            font-size: 3.75rem;
            color: #dc3545;
            margin-bottom: 20px;
        }

        .wrapper-blocked h4,
        .wrapper-blocked h5,
        .wrapper-blocked p {
            color: #dc3545 !important;
        }

        .wrapper-blocked h4 {
            margin-bottom: 10px;
        }

        .wrapper-blocked h5 {
            margin-bottom: 15px;
        }

        .wrapper-blocked p {
            margin-bottom: 20px;
        }
        
        h2, h3, h4 {
            color: #333;
            margin-bottom: 20px;
        }
        
        p {
            color: #333;
        }
        
        label {
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            border: 2px solid #dc3545;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.3);
            background-color: #fff;
            color: #333;
        }
        
        .form-control::placeholder {
            color: #888;
        }
        
        .form-control-lg {
            border: 2px solid #dc3545;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .form-control-lg:focus {
            border-color: #dc3545;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.3);
            background-color: #fff;
            color: #333;
        }
        
        .form-control-lg::placeholder {
            color: #888;
        }
        
        .security-badge {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .security-badge .shield-icon {
            font-size: 3.75rem;
            color: #dc3545;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .question-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #dc3545;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .question-box .question-icon {
            font-size: 2.1875rem;
            color: #dc3545;
            margin-bottom: 10px;
        }
        
        .question-box .question-text {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
        }
        
        .question-box small {
            color: #888;
        }
        
        .attempts-badge {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid #dc3545;
            color: #333;
        }
        
        .attempts-badge .remaining {
            font-weight: bold;
            color: #dc3545;
            font-size: 1.125rem;
        }
        
        .attempts-badge .danger {
            color: #dc3545;
            font-weight: bold;
            font-size: 1.125rem;
        }
        
        .btn-outline-danger {
            border: 2px solid #dc3545 !important;
            color: #dc3545 !important;
            background-color: transparent !important;
        }
        
        .btn-outline-danger:hover {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #ffffff !important;
            box-shadow: 0 0 15px rgba(220, 53, 69, 0.5) !important;
        }
        
        .btn-outline-danger:focus {
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.3) !important;
        }
        
        .btn-primary {
            background-color: #dc3545;
            border-color: #dc3545;
            padding: 8px 20px;
        }
        
        .btn-primary:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            padding: 8px 20px;
        }
        
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            padding: 8px 20px;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        .btn-lg {
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
        }
        
        a {
            color: #dc3545;
        }
        
        a:hover {
            color: #c82333;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        
        .alert-danger .close {
            opacity: 0.8;
        }
        
        .invalid-feedback {
            color: #dc3545;
        }
        
        .page-indicator {
            text-align: center;
            color: #888;
            font-size: 0.75rem;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <!-- ===== CONTROLES NO TOPO ===== -->
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

        <!-- ===== EXIBIÇÃO DO LOGIN NO CANTO SUPERIOR DIREITO (APÓS LOGIN) ===== -->
        <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
        <div style="width: 1px; height: 30px; background: #ddd;"></div>
        <div style="display: flex; align-items: center; gap: 10px; margin-left: auto;">
            <i class="fas fa-user" style="color: #dc3545;"></i>
            <span style="font-weight: 600; color: #dc3545;">
                <?php echo htmlspecialchars($_SESSION["nome"] ?? $_SESSION["username"]); ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <?php if($blocked_message): ?>
    <!-- ===== MENSAGEM DE BLOQUEIO ===== -->
    <div class="wrapper-blocked">
        <i class="fas fa-exclamation-triangle warning-icon"></i>
        <h4>3 tentativas sem sucesso!</h4>
        <h5>Favor realizar Login novamente.</h5>
        <p><?php echo $blocked_message; ?></p>
        <a href="login.php" class="btn btn-outline-danger btn-block btn-lg">
            <i class="fas fa-sign-in-alt"></i> Voltar para Login
        </a>
    </div>
    <?php endif; ?>

    <?php if(!$blocked_message): ?>
    <div class="wrapper">
        <?php if($show_2fa && $current_question): ?>
            <!-- ===== TELA DE VERIFICAÇÃO 2FA ===== -->
            <div class="security-badge">
                <i class="fas fa-shield-alt shield-icon"></i>
                <h3 class="mt-2">Verificação de Segurança</h3>
                <p>Autenticação de Dois Fatores (2FA)</p>
            </div>
            
            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> ' . $login_err . '
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                      </div>';
            }
            ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
                <input type="hidden" name="2fa_submit" value="1">
                
                <div class="question-box">
                    <i class="fas <?php echo $current_question['icone']; ?> question-icon"></i>
                    <div class="question-text"><?php echo $current_question['texto']; ?></div>
                    <?php if($current_question['campo'] === 'cep'): ?>
                        <small class="d-block mt-2" style="color: #888;">Digite apenas os números</small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-pencil-alt"></i> Sua Resposta:</label>
                    
                    <?php if($current_question['tipo'] === 'date'): ?>
                        <input type="date" 
                               name="2fa_answer" 
                               class="form-control form-control-lg" 
                               required
                               autofocus>
                    
                    <?php elseif($current_question['campo'] === 'cep'): ?>
                        <input type="text" 
                               name="2fa_answer" 
                               id="cep_input"
                               class="form-control form-control-lg" 
                               required
                               placeholder="<?php echo $current_question['placeholder']; ?>"
                               maxlength="8"
                               autocomplete="off"
                               autofocus
                               inputmode="numeric">
                    
                    <?php else: ?>
                        <input type="text" 
                               name="2fa_answer" 
                               class="form-control form-control-lg" 
                               required
                               placeholder="<?php echo $current_question['placeholder']; ?>"
                               autocomplete="off"
                               autofocus>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-danger btn-block btn-lg">
                        <i class="fas fa-check-circle"></i> Verificar Resposta
                    </button>
                </div>
                
                <div class="attempts-badge">
                    <i class="fas fa-key"></i> Tentativas restantes: 
                    <span class="<?php echo ($_SESSION['2fa_attempts'] <= 1) ? 'danger' : 'remaining'; ?>">
                        <?php echo $_SESSION['2fa_attempts'] ?? 3; ?>
                    </span>
                </div>
            </form>                        
            
        <?php else: ?>
            <!-- ===== TELA DE LOGIN NORMAL ===== -->
            <h2 class="text-center"><i class="fas fa-user-circle"></i> Login</h2>
            <p class="text-center">Por favor, preencha os campos para fazer o login.</p>
            
            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> ' . $login_err . '
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                      </div>';
            }
            ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Login</label>
                    <input type="text" name="login" class="form-control form-control-lg <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" maxlength="6" placeholder="Digite seu login">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>    
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Senha</label>
                    <input type="password" name="senha" class="form-control form-control-lg <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Digite sua senha" maxlength="8">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-outline-danger btn-block btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </button>
                </div>
                
                <div class="form-group">
                    <!-- CORREÇÃO: type="button" em vez de type="reset" -->
                    <button type="button" class="btn btn-outline-danger btn-block btn-lg" onclick="limparFormulario()">
                        <i class="fas fa-eraser"></i> Limpar
                    </button>
                </div>
                
                <p class="text-center btn-lg">Não tem uma conta? <a href="cadastro.php">Inscreva-se agora</a>.</p>
            </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ===== SCRIPTS ===== -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            $('#cep_input').on('input', function() {
                var valor = $(this).val().replace(/\D/g, '');
                if(valor.length > 8) {
                    valor = valor.substring(0, 8);
                }
                $(this).val(valor);
            });
        });

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
        
        // ===== FUNÇÃO CORRIGIDA PARA LIMPAR O FORMULÁRIO =====
        function limparFormulario() {
            // Limpa o campo login
            document.querySelector('input[name="login"]').value = '';
            // Limpa o campo senha
            document.querySelector('input[name="senha"]').value = '';
        }
    </script>
    
</body>
</html>