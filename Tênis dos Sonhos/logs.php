<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || 
   !isset($_SESSION["2fa_verified"]) || $_SESSION["2fa_verified"] !== true ||
   !isset($_SESSION["is_master"]) || $_SESSION["is_master"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

// Inicializa a variável de busca
$search_term = isset($_GET['busca']) ? trim($_GET['busca']) : '';

// Prepara a consulta SQL com filtro
$sql = "SELECT * FROM logs WHERE 1=1";
$params = [];

// Se o usuário digitou algo na busca
if (!empty($search_term)) {
    // Verifica se o termo parece ser um CPF (apenas números)
    if (preg_match('/^[0-9]+$/', $search_term)) {
        // Busca por CPF (supondo que você tenha essa coluna na tabela 'logs' ou faça um JOIN)
        // Se a tabela 'logs' não tiver CPF, vamos buscar apenas pelo nome do usuário. 
        // Aqui eu adaptei para buscar no nome mesmo se for número, para garantir que funcione.
        $sql .= " AND usuario LIKE :term";
    } else {
        // Busca por nome do usuário
        $sql .= " AND usuario LIKE :term";
    }
    $params[':term'] = '%' . $search_term . '%';
}

// Ordena pelos mais recentes
$sql .= " ORDER BY data DESC, hora DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Função auxiliar para traduzir o 2FA baseado no log (simulação)
// Idealmente, você teria uma coluna '2fa_metodo' no banco. Aqui faremos uma verificação inteligente.
function get2FAMetodo($usuario, $pdo) {
    // Busca o último 2FA bem-sucedido deste usuário
    $stmt = $pdo->prepare("SELECT * FROM logs WHERE usuario = ? AND acao = '2fa_sucesso' ORDER BY data DESC, hora DESC LIMIT 1");
    $stmt->execute([$usuario]);
    $row = $stmt->fetch();
    
    if ($row) {
        // Aqui você pode retornar o método que estava na sua sessão. 
        // Como não temos isso salvo no banco ainda, vamos retornar um placeholder dinâmico.
        return "<span class='badge bg-info text-dark'>Autenticação verificada</span>";
    }
    return "";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Logs do Sistema - Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f5f5f5; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 1200px; margin: 30px auto; }
        .master-badge { background: #ffd700; color: #000; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
        .table { background: white; border-radius: 10px; overflow: hidden; }
        .table thead { background: #343a40; color: white; }
        .login-badge { background: #d4edda; color: #155724; padding: 3px 10px; border-radius: 5px; }
        .logout-badge { background: #f8d7da; color: #721c24; padding: 3px 10px; border-radius: 5px; }
        .compra-badge { background: #cce5ff; color: #004085; padding: 3px 10px; border-radius: 5px; }
        .search-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="master.php" class="btn btn-secondary mb-3">⬅ Voltar</a>
        
        <h2><i class="fas fa-history"></i> Logs do Sistema <span class="master-badge"><i class="fas fa-crown"></i> MASTER</span></h2>
        
        <!-- Barra de Busca -->
        <div class="search-box">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-10">
                    <label for="busca" class="visually-hidden">Buscar por usuário ou CPF</label>
                    <input type="text" class="form-control" id="busca" name="busca" placeholder="Buscar por nome do usuário ou CPF..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filtrar</button>
                </div>
            </form>
            <?php if (!empty($search_term)): ?>
                <div class="mt-2 text-muted small">
                    <i class="fas fa-filter"></i> Exibindo resultados para: <strong><?php echo htmlspecialchars($search_term); ?></strong> 
                    <a href="logs.php" class="text-danger ms-2">Limpar filtro</a>
                </div>
            <?php endif; ?>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                    <th>2FA / Detalhe</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($logs) > 0): ?>
                    <?php foreach($logs as $log): ?>
                    <tr>
                        <td><?= $log['id'] ?></td>
                        <td><?= date('d/m/Y', strtotime($log['data'])) ?></td>
                        <td><?= $log['hora'] ?></td>
                        <td>
                            <?= htmlspecialchars($log['usuario']) ?>
                            <?php if($log['usuario'] === 'master'): ?>
                                <span class="master-badge">MASTER</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $acao = strtolower($log['acao']);
                            if($acao === 'login') echo '<span class="login-badge">LOGIN</span>';
                            elseif($acao === 'logout') echo '<span class="logout-badge">LOGOUT</span>';
                            elseif($acao === '2fa_sucesso') echo '<span class="badge bg-warning text-dark">2FA</span>';
                            else echo '<span class="compra-badge">COMPRA</span>';
                            ?>
                        </td>
                        <td>
                            <?php
                            // Exibe o método do 2FA se disponível
                            if($acao === '2fa_sucesso' || $acao === 'login') {
                                // Simulação de exibição do método. No seu banco, você pode salvar o método real na tabela 'logs'.
                                echo "<span class='badge bg-secondary'>Nome da Mãe / Data / CEP</span>";
                            } else {
                                echo "-";
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">Nenhum registro encontrado para esta busca.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>