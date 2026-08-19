<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || 
   !isset($_SESSION["2fa_verified"]) || $_SESSION["2fa_verified"] !== true ||
   !isset($_SESSION["is_master"]) || $_SESSION["is_master"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

// Obter o nome do arquivo atual
$current_file = basename($_SERVER['PHP_SELF']);

// Processar exclusão de usuário
if(isset($_GET['delete_id']) && $_GET['delete_id'] != 3) { // ID 3 é o MASTER SYSTEM
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM cadastro WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("location: " . $current_file);
    exit;
}

// Processar edição de nome
if(isset($_POST['edit_id']) && isset($_POST['novo_nome_completo'])) {
    $edit_id = $_POST['edit_id'];
    $novo_nome = trim($_POST['novo_nome_completo']);
    if(!empty($novo_nome)) {
        $stmt = $pdo->prepare("UPDATE cadastro SET nome_completo = ? WHERE id = ?");
        $stmt->execute([$novo_nome, $edit_id]);
    }
    header("location: " . $current_file);
    exit;
}

// --- LÓGICA DE BUSCA (NOME OU LOGIN) ---
$search_term = isset($_GET['busca']) ? trim($_GET['busca']) : '';

$sql = "SELECT id, nome_completo, login, email, cpf, data_de_nascimento, created_at FROM cadastro WHERE 1=1";
$params = [];

if (!empty($search_term)) {
    // Busca por Nome OU Login
    $sql .= " AND (nome_completo LIKE :term OR login LIKE :term)";
    $params[':term'] = '%' . $search_term . '%';
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();
// --- FIM DA LÓGICA DE BUSCA ---
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários - Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f5f5f5; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 1200px; margin: 30px auto; }
        .master-badge { background: #ffd700; color: #000; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
        .table { background: white; border-radius: 10px; overflow: hidden; }
        .table thead { background: #343a40; color: white; }
        .btn-voltar { margin-bottom: 20px; }
        .action-icons { display: flex; gap: 8px; align-items: center; }
        .action-icons a { text-decoration: none; }
        .btn-icon { padding: 2px 6px; font-size: 0.9rem; border-radius: 4px; }
        .btn-icon:hover { opacity: 0.8; }
        .icon-edit { color: #007bff; cursor: pointer; }
        .icon-delete { color: #dc3545; cursor: pointer; }
        .modal-header { background: #343a40; color: white; }
        .btn-edit { background: none; border: none; color: #007bff; padding: 0; }
        .btn-edit:hover { color: #0056b3; }
        .btn-delete { background: none; border: none; color: #dc3545; padding: 0; }
        .btn-delete:hover { color: #a71d2a; }
        .alert-success { margin-top: 20px; }
        
        .search-box { background: white; padding: 15px 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> Nome atualizado com sucesso!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> Usuário excluído com sucesso!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <a href="master.php" class="btn btn-secondary btn-voltar">⬅ Voltar</a>
        
        <h2><i class="fas fa-users"></i> Gerenciar Usuários <span class="master-badge"><i class="fas fa-crown"></i> MASTER</span></h2>
        
        <!-- BARRA DE BUSCA -->
        <div class="search-box">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-10">
                    <label for="busca" class="visually-hidden">Buscar por nome ou login</label>
                    <input type="text" class="form-control" id="busca" name="busca" placeholder="Buscar por Nome ou Login..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Buscar</button>
                </div>
            </form>
            
            <?php if (!empty($search_term)): ?>
                <div class="mt-2 text-muted small">
                    <i class="fas fa-filter"></i> Exibindo resultados para: <strong><?php echo htmlspecialchars($search_term); ?></strong> 
                    (<?php echo count($usuarios); ?> usuário(s) encontrado(s))
                    <a href="usuarios.php" class="text-danger ms-2">Limpar filtro</a>
                </div>
            <?php endif; ?>
        </div>

        <p>Total de usuários cadastrados: <b><?= count($usuarios) ?></b></p>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Login</th>
                    <th>Email</th>
                    <th>CPF</th>
                    <th>Data Nasc.</th>
                    <th>Cadastrado em</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($usuarios) > 0): ?>
                    <?php foreach($usuarios as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td>
                            <span class="user-name"><?= htmlspecialchars($user['nome_completo']) ?></span>
                            <?php if($user['login'] !== 'master'): ?>
                                <button class="btn-edit ms-2" onclick="openEditModal(<?= $user['id'] ?>, '<?= addslashes(htmlspecialchars($user['nome_completo'])) ?>')" title="Editar nome">
                                    <i class="fas fa-edit icon-edit"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $user['login'] ?>
                            <?php if($user['login'] === 'master'): ?>
                                <span class="master-badge">MASTER</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['cpf']) ?></td>
                        <td><?= date('d/m/Y', strtotime($user['data_de_nascimento'])) ?></td>
                        <td>
                            <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                            <?php if($user['login'] !== 'master'): ?>
                                <button class="btn-delete ms-2" onclick="confirmDelete(<?= $user['id'] ?>)" title="Excluir usuário">
                                    <i class="fas fa-trash icon-delete"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-search me-2"></i> Nenhum usuário encontrado com este Nome ou Login.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para editar nome -->
    <div class="modal fade" id="editNameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Nome do Usuário</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="mb-3">
                            <label for="novo_nome" class="form-label">Novo Nome</label>
                            <input type="text" class="form-control" name="novo_nome_completo" id="novo_nome" required maxlength="255">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEditModal(id, nome) {
            document.getElementById('edit_id').value = id;
            document.getElementById('novo_nome').value = nome;
            var modal = new bootstrap.Modal(document.getElementById('editNameModal'));
            modal.show();
        }

        function confirmDelete(id) {
            if(confirm('Tem certeza que deseja excluir este usuário?')) {
                window.location.href = '<?php echo basename($_SERVER['PHP_SELF']); ?>?delete_id=' + id;
            }
        }
    </script>
</body>
</html>