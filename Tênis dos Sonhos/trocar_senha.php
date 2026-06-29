<?php
require_once "config.php";

// Buscar master
$stmt = $pdo->prepare("SELECT * FROM cadastro WHERE login = ?");
$stmt->execute(['master']);
$master = $stmt->fetch();

if($master) {
    // Nova senha: aaabbaaa (8 letras)
    $nova_senha = 'aaabbaaa';
    $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    // Atualizar no banco
    $stmt = $pdo->prepare("UPDATE cadastro SET senha = ? WHERE login = ?");
    $stmt->execute([$novo_hash, 'master']);
    
    echo "✅ PRONTO!<br><br>";
    echo "Login: <b>master</b><br>";
    echo "Senha: <b>aaabbaaa</b><br><br>";
    echo "2FA:<br>";
    echo "Mãe: <b>MASTER MOTHER</b><br>";
    echo "Data: <b>01/01/2000</b><br>";
    echo "CEP: <b>00000000</b><br><br>";
    echo "<a href='login.php'>IR PARA LOGIN</a>";
}
?>