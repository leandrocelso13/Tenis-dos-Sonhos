<?php
/* Credenciais do banco de dados. Supondo que você esteja executando o MySQL
servidor com configuração padrão (usuário 'root' sem senha) 
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'teste_db');*/

/* 
define('DB_SERVER', 'database-mariadb-1.xxxxxxxxxxxxx.us-east-1.rds.amazonaws.com');
define('DB_USERNAME', 'admin');
define('DB_PASSWORD', '');
define('DB_NAME', '')
*/
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'teste_db');
 
/* Tentativa de conexão com o banco de dados MySQL */
try{
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Para testar a conexão com o banco
    //echo "<h2>Config = " .$pod. "</h2>";
    // Defina o modo de erro PDO para exceção
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("ERROR: Não foi possível conectar." . $e->getMessage());
}
?>