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

// Prepara a consulta SQL com filtro apenas por usuário (login)
$sql = "SELECT * FROM logs WHERE 1=1";
$params = [];

// Se o usuário digitou algo na busca, filtra pelo campo 'usuario'
if (!empty($search_term)) {
    $sql .= " AND usuario LIKE :term";
    $params[':term'] = '%' . $search_term . '%';
}

// Ordena pelos mais recentes
$sql .= " ORDER BY data DESC, hora DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();
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
        .login-badge { background: #d4edda; color: #155724; padding: 3px 10px; border-radius: 5px; font-weight: bold; }
        .logout-badge { background: #f8d7da; color: #721c24; padding: 3px 10px; border-radius: 5px; font-weight: bold; }
        .search-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
        
        /* Estilos para os tipos de 2FA - COR PRETA NO TEXTO */
        .badge-2fa-nome { background: #e8d5f5; color: #000000; padding: 3px 10px; border-radius: 5px; font-weight: bold; display: inline-block; }
        .badge-2fa-cep { background: #d1ecf1; color: #000000; padding: 3px 10px; border-radius: 5px; font-weight: bold; display: inline-block; }
        .badge-2fa-data { background: #fff3cd; color: #000000; padding: 3px 10px; border-radius: 5px; font-weight: bold; display: inline-block; }
        .badge-2fa-unknown { background: #e2e3e5; color: #000000; padding: 3px 10px; border-radius: 5px; font-weight: bold; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <a href="master.php" class="btn btn-secondary mb-3">⬅ Voltar</a>
        
        <h2><i class="fas fa-history"></i> Logs do Sistema <span class="master-badge"><i class="fas fa-crown"></i> MASTER</span></h2>
        
        <!-- Barra de Busca APENAS por Login -->
        <div class="search-box">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-10">
                    <label for="busca" class="visually-hidden">Buscar por usuário</label>
                    <input type="text" class="form-control" id="busca" name="busca" placeholder="Buscar pelo usuário..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filtrar</button>
                </div>
            </form>
            <?php if (!empty($search_term)): ?>
                <div class="mt-2 text-muted small">
                    <i class="fas fa-filter"></i> Exibindo logs para o usuário: <strong><?php echo htmlspecialchars($search_term); ?></strong> 
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
                    <th>2FA</th>
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
                            
                            // NA COLUNA AÇÃO: SÓ LOGIN OU LOGOUT
                            if($acao === 'login') {
                                echo '<span class="login-badge">LOGIN</span>';
                            } elseif($acao === 'logout') {
                                echo '<span class="logout-badge">LOGOUT</span>';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            // NA COLUNA 2FA: Mostra o tipo de 2FA
                            if ($acao === 'login' && !empty($log['tipo_2fa'])) {
                                $tipo = strtolower($log['tipo_2fa']);
                                $label = "";
                                $class = "";
                                $icon = "";
                                $cor_extra = ""; // Variável para cor extra
                                
                                switch($tipo) {
                                    case 'nome_materno':
                                        $label = "Nome da Mãe";
                                        $class = "badge-2fa-nome";
                                        $icon = "fa-user";
                                        break;
                                    case 'cep':
                                        $label = "CEP";
                                        $class = "badge-2fa-cep";
                                        $icon = "fa-map-marker-alt"; // Ícone sólido
                                        $cor_extra = "text-primary"; // Cor azul verdadeira (Bootstrap)
                                        break;
                                    case 'data_de_nascimento':
                                        $label = "Data de Nascimento";
                                        $class = "badge-2fa-data";
                                        // O ícone será tratado fora do switch
                                        break;
                                    default:
                                        $label = ucfirst(str_replace('_', ' ', $tipo));
                                        $class = "badge-2fa-unknown";
                                        $icon = "fa-shield-alt";
                                }
                                
                                // Exibe o ícone correto
                                if ($tipo === 'data_de_nascimento') {
                                    echo "<span class='$class'>📅 $label</span>";
                                } elseif (!empty($cor_extra)) {
                                    // Se tiver cor extra (caso do CEP azul)
                                    echo "<span class='$class'><i class='fas $icon $cor_extra'></i> $label</span>";
                                } else {
                                    // Demais ícones (Nome da Mãe, etc.)
                                    echo "<span class='$class'><i class='fas $icon'></i> $label</span>";
                                }
                            } elseif ($acao === 'login' && empty($log['tipo_2fa'])) {
                                // Login sem 2FA (logs muito antigos ou erro)
                                echo "-";
                            } else {
                                // Logout ou outras ações
                                echo "-";
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">Nenhum registro encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>