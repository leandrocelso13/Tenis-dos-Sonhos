<?php
require_once "config.php";

$username = "usuario1"; // Coloque o usuário do banco
$password = "123456";  // Coloque a senha

$sql = "SELECT * FROM users WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->execute(['username' => $username]);

if($row = $stmt->fetch()){
    echo "Usuário encontrado: " . $row['username'] . "<br>";
    echo "Hash salva: " . $row['password'] . "<br>";
    
    if(password_verify($password, $row['password'])){
        echo "✅ SENHA CORRETA!<br>";
    } else {
        echo "❌ SENHA INCORRETA!<br>";
        // Teste se é hash válida
        if(password_get_info($row['password'])['algo'] === 0){
            echo "⚠️ AVISO: A senha NÃO está em formato hash!<br>";
        }
    }
} else {
    echo "Usuário não encontrado!<br>";
}
?>