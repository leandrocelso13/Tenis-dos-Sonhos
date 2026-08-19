<?php
require_once "config.php";

// Dados do master
$login = 'master';
$senha = 'aaabbaaa';

// 1. Apaga o master antigo (garante que não tem duplicata)
$pdo->exec("DELETE FROM cadastro WHERE login = '$login'");

// 2. Gera o hash usando a função nativa do PHP
$hash = password_hash($senha, PASSWORD_DEFAULT);

// 3. Insere o master NOVO com o hash gerado AGORA
$sql = "INSERT INTO cadastro (login, senha, nome_completo, nome_materno, data_de_nascimento, cep) 
        VALUES (?, ?, 'Administrador Master', 'MASTER MOTHER', '2000-01-01', '00000000')";
$stmt = $pdo->prepare($sql);

if($stmt->execute([$login, $hash])){
    echo "<h1 style='color:green;'>✅ SUCESSO ABSOLUTO!</h1>";
    echo "<h3>O usuário master foi recriado com a senha <b>'aaabbaaa'</b>.</h3>";
    echo "<p>O hash gerado foi: <pre style='background:#eee; padding:10px;'>$hash</pre></p>";
    echo "<a href='login.php' style='padding:10px 20px; background:#dc3545; color:white; text-decoration:none; border-radius:5px;'>CLIQUE AQUI PARA FAZER O LOGIN</a>";
} else {
    echo "❌ Erro ao inserir no banco.";
}
?>