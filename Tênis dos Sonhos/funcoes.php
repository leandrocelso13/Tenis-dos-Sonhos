<?php
// funcoes.php
// Funções auxiliares para log e verificação master

/**
 * Registra uma ação no log
 */
function registrarLog($pdo, $usuario, $acao) {
    $stmt = $pdo->prepare("INSERT INTO logs (data, hora, usuario, acao) VALUES (CURDATE(), CURTIME(), ?, ?)");
    $stmt->execute([$usuario, $acao]);
}

/**
 * Verifica se o usuário é master
 */
function isMaster($login) {
    return ($login === 'master');
}

/**
 * Buscar todos os logs (para tela master)
 */
function getLogs($pdo, $limit = 100) {
    $stmt = $pdo->query("SELECT * FROM logs ORDER BY data DESC, hora DESC LIMIT $limit");
    return $stmt->fetchAll();
}
?>