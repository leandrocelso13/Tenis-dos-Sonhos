<?php
// funcoes.php
// Funções auxiliares para log e verificação master

/**
 * Registra uma ação no log
 * @param PDO $pdo Conexão com o banco
 * @param string $usuario Nome do usuário
 * @param string $acao Ação realizada (login, logout, 2fa_sucesso, etc.)
 * @param string|null $tipo_2fa Tipo de 2FA utilizado (nome_materno, cep, data_de_nascimento)
 */
function registrarLog($pdo, $usuario, $acao, $tipo_2fa = null) {
    // Verifica se a coluna tipo_2fa existe, se não existir, adiciona
    try {
        $check = $pdo->query("SHOW COLUMNS FROM logs LIKE 'tipo_2fa'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE logs ADD COLUMN tipo_2fa VARCHAR(50) DEFAULT NULL");
        }
    } catch (PDOException $e) {
        // Se houver erro, continua sem tipo_2fa
    }
    
    // Insere o log com ou sem tipo_2fa
    if ($tipo_2fa !== null) {
        $stmt = $pdo->prepare("INSERT INTO logs (data, hora, usuario, acao, tipo_2fa) VALUES (CURDATE(), CURTIME(), ?, ?, ?)");
        $stmt->execute([$usuario, $acao, $tipo_2fa]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO logs (data, hora, usuario, acao) VALUES (CURDATE(), CURTIME(), ?, ?)");
        $stmt->execute([$usuario, $acao]);
    }
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