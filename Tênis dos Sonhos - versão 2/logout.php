<?php
// Inicialize a sessão
session_start();

// Incluir arquivos necessários para registrar log
require_once "config.php";
require_once "funcoes.php";

// Registrar log de logout se o usuário estava logado
// Verifica se a variável de sessão 'username' existe antes de registrar
if(isset($_SESSION["username"])) {
    registrarLog($pdo, $_SESSION["username"], 'logout');
}

// Remova todas as variáveis de sessão
$_SESSION = array();

// Destrua a sessão.
session_destroy();

// Redirecionar para a página de login
header("location: login.php");
exit;
?>