<?php
require_once "config.php";

// Login e senha que você está tentando (substitua pelos valores reais)
$login_teste = "abcdef"; // Coloque o login que você cadastrou
$senha_teste = "abcdefgh"; // Coloque a senha que você cadastrou

echo "<h3>Teste de Login</h3>";

// 1. Verificar se a tabela cadastro existe
$stmt = $pdo->query("SHOW TABLES LIKE 'cadastro'");
if($stmt->rowCount() > 0) {
    echo "✓ Tabela 'cadastro' existe<br>";
} else {
    echo "✗ Tabela 'cadastro' NÃO existe<br>";
}

// 2. Mostrar todos os registros da tabela
$stmt = $pdo->query("SELECT id, nome, login, senha FROM cadastro");
echo "<h4>Registros na tabela:</h4>";
while($row = $stmt->fetch()) {
    echo "ID: {$row['id']}<br>";
    echo "Nome: {$row['nome']}<br>";
    echo "Login: '{$row['login']}' (tamanho: ".strlen($row['login']).")<br>";
    echo "Senha (hash): {$row['senha']}<br>";
    echo "<hr>";
}

// 3. Testar a busca pelo login
$stmt = $pdo->prepare("SELECT * FROM cadastro WHERE login = ?");
$stmt->execute([$login_teste]);
$usuario = $stmt->fetch();

echo "<h4>Testando login: '$login_teste'</h4>";

if($usuario) {
    echo "✓ Usuário encontrado!<br>";
    echo "Login no banco: '{$usuario['login']}'<br>";
    
    // 4. Verificar a senha
    if(password_verify($senha_teste, $usuario['senha'])) {
        echo "✓ Senha CORRETA!<br>";
    } else {
        echo "✗ Senha INCORRETA!<br>";
        echo "Senha testada: '$senha_teste'<br>";
        echo "Hash no banco: {$usuario['senha']}<br>";
    }
} else {
    echo "✗ Usuário NÃO encontrado com login: '$login_teste'<br>";
    echo "Verifique se digitou exatamente 6 letras<br>";
}

// 5. Mostrar estrutura da tabela
echo "<h4>Estrutura da tabela:</h4>";
$stmt = $pdo->query("DESCRIBE cadastro");
while($row = $stmt->fetch()) {
    echo "{$row['Field']} - {$row['Type']}<br>";
}
?>