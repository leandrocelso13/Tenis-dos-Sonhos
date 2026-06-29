<?php
require_once "config.php";

$erro = "";
$sucesso = "";

// Função para validar CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) {
        return false;
    }
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$t] != $d) {
            return false;
        }
    }
    return true;
}

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $campos_obrigatorios = ['nome', 'data_nascimento', 'sexo', 'nome_materno', 'cpf', 'email', 
                           'telefone_celular', 'telefone_fixo', 'cep', 'logradouro', 'numero',
                           'bairro', 'cidade', 'estado', 'login', 'senha', 'confirmar_senha'];
    
    $todos_preenchidos = true;
    foreach($campos_obrigatorios as $campo) {
        if(empty($_POST[$campo])) {
            $todos_preenchidos = false;
            break;
        }
    }
    
    if(!$todos_preenchidos) {
        $erro = "Todos os campos obrigatórios devem ser preenchidos.";
    }
    elseif(strlen($_POST['nome']) < 15 || strlen($_POST['nome']) > 80) {
        $erro = "O nome deve ter entre 15 e 80 caracteres.";
    }
    // CORREÇÃO 1: Validação de nome com todos os caracteres acentuados e cedilha
    elseif(!preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/", $_POST['nome'])) {
        $erro = "O nome deve conter apenas caracteres alfabéticos.";
    }
    elseif(!validarCPF($_POST['cpf'])) {
        $erro = "CPF inválido.";
    }
    elseif(!preg_match("/^\(\+55\)\d{2}-\d{8,9}$/", $_POST['telefone_celular'])) {
        $erro = "Telefone celular deve estar no formato (+55)XX-XXXXXXXX";
    }
    elseif(!preg_match("/^\(\+55\)\d{2}-\d{8}$/", $_POST['telefone_fixo'])) {
        $erro = "Telefone fixo deve estar no formato (+55)XX-XXXXXXXX";
    }
    elseif(strlen($_POST['login']) != 6 || !preg_match("/^[A-Za-z]+$/", $_POST['login'])) {
        $erro = "O login deve ter exatamente 6 caracteres alfabéticos.";
    }
    elseif(strlen($_POST['senha']) != 8 || !preg_match("/^[A-Za-z]+$/", $_POST['senha'])) {
        $erro = "A senha deve ter exatamente 8 caracteres alfabéticos.";
    }
    elseif($_POST['senha'] != $_POST['confirmar_senha']) {
        $erro = "As senhas não conferem.";
    }
    elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";
    }
    else {
        try {
            $stmt = $pdo->prepare("INSERT INTO cadastro (
                nome_completo, data_de_nascimento, sexo, nome_materno, cpf, email,
                telefone_celular, telefone_fixo, cep, logradouro, numero,
                complemento, bairro, cidade, estado, login, senha
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $_POST['nome'],
                $_POST['data_nascimento'],
                $_POST['sexo'],
                $_POST['nome_materno'],
                $_POST['cpf'],
                $_POST['email'],
                $_POST['telefone_celular'],
                $_POST['telefone_fixo'],
                $_POST['cep'],
                $_POST['logradouro'],
                $_POST['numero'],
                $_POST['complemento'] ?? '',
                $_POST['bairro'],
                $_POST['cidade'],
                $_POST['estado'],
                $_POST['login'],
                password_hash($_POST['senha'], PASSWORD_DEFAULT)
            ]);
            
            $sucesso = "Cadastro realizado com sucesso! Redirecionando...";
            header("refresh:2;url=login.php");
        } catch(PDOException $e) {
            $erro = "Erro ao cadastrar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<title>Cadastro</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<style>
/* ===== TAMANHO DA FONTE BASE ===== */
html {
    font-size: 100%;
}

body { 
    font-size: 1rem;
    background-color: #ffffff;
    min-height: 100vh;
    width: 100%;
    margin: 0;
    padding: 20px;
    display: block;
    padding-top: 20px;
}

/* ===== CONTROLES NO TOPO ===== */
.controls-container {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding: 8px 15px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 2px solid #dc3545;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    width: 100%;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
    box-sizing: border-box;
}

.controls-container .btn-font {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1rem;
    border: 2px solid #dc3545;
    color: #dc3545;
    background: white;
    transition: all 0.3s;
    cursor: pointer;
}

.controls-container .btn-font:hover {
    background: #dc3545;
    color: white;
}

.controls-container .font-percent {
    font-size: 0.9rem;
    font-weight: 700;
    color: #dc3545;
    min-width: 45px;
    text-align: center;
}

.switch {
    position: relative;
    display: inline-block;
    width: 45px;
    height: 26px;
}

.switch input { 
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: #dc3545;
}

input:checked + .slider:before {
    transform: translateX(19px);
}

.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}

.font-size-display {
    font-size: 0.8rem;
    color: #333;
    font-weight: 600;
}

/* ===== WRAPPER (JANELA DE CADASTRO) ===== */
.wrapper {
    width: 800px;
    padding: 30px;
    background-color: #ffffff;
    border-radius: 10px;
    border: 2px solid #dc3545;
    box-shadow: 0 0 30px rgba(220, 53, 69, 0.3);
    transition: all 0.3s ease;
    margin: 0 auto;
}

h4, h5 {
    color: #333;
    margin-bottom: 20px;
}

label {
    font-weight: 600;
    color: #333;
}

.form-control {
    border: 2px solid #dc3545;
    background-color: #f8f9fa;
    color: #333;
}

.form-control:focus {
    border-color: #dc3545;
    box-shadow: 0 0 10px rgba(220, 53, 69, 0.3);
    background-color: #fff;
    color: #333;
}

.form-control::placeholder {
    color: #888;
}

.form-control option {
    background-color: #fff;
    color: #333;
}

select.form-control {
    color: #333;
}

.btn-outline-danger {
    border: 2px solid #dc3545 !important;
    color: #dc3545 !important;
    background-color: transparent !important;
}

.btn-outline-danger:hover {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: #ffffff !important;
    box-shadow: 0 0 15px rgba(220, 53, 69, 0.5) !important;
}

.btn-outline-danger:focus {
    box-shadow: 0 0 10px rgba(220, 53, 69, 0.3) !important;
}

.btn-lg {
    border-radius: 10px;
    padding: 12px;
    font-weight: 600;
}

a {
    color: #dc3545;
}

a:hover {
    color: #c82333;
}

.alert-danger {
    background-color: #f8d7da;
    border: 2px solid #dc3545;
    color: #721c24;
}

.alert-success {
    background-color: #d4edda;
    border: 2px solid #28a745;
    color: #155724;
}

.invalid-feedback {
    color: #dc3545;
}

/* ===== MODO ESCURO ===== */
[data-bs-theme="dark"] body {
    background-color: #1a1a1a !important;
}

[data-bs-theme="dark"] .wrapper {
    background-color: #2d2d2d !important;
    border-color: #dc3545 !important;
}

[data-bs-theme="dark"] h4, 
[data-bs-theme="dark"] h5,
[data-bs-theme="dark"] label {
    color: #ffffff !important;
}

[data-bs-theme="dark"] .form-control {
    background-color: #3d3d3d !important;
    color: #ffffff !important;
    border-color: #dc3545 !important;
}

[data-bs-theme="dark"] .form-control:focus {
    background-color: #3d3d3d !important;
    color: #ffffff !important;
    border-color: #dc3545 !important;
    box-shadow: 0 0 10px rgba(220, 53, 69, 0.3) !important;
}

[data-bs-theme="dark"] .form-control::placeholder {
    color: #aaaaaa !important;
}

[data-bs-theme="dark"] .form-control option {
    background-color: #3d3d3d !important;
    color: #ffffff !important;
}

[data-bs-theme="dark"] select.form-control {
    color: #ffffff !important;
}

[data-bs-theme="dark"] .alert-danger {
    background-color: #2d0000 !important;
    border-color: #dc3545 !important;
    color: #ff6666 !important;
}

[data-bs-theme="dark"] .alert-success {
    background-color: #002d00 !important;
    border-color: #00ff00 !important;
    color: #66ff66 !important;
}

[data-bs-theme="dark"] a {
    color: #ff6b6b !important;
}

[data-bs-theme="dark"] .btn-outline-danger {
    color: #ffffff !important;
    border-color: #dc3545 !important;
}

[data-bs-theme="dark"] .btn-outline-danger:hover {
    background-color: #dc3545 !important;
    color: #ffffff !important;
}

/* ===== MENSAGENS FLUTUANTES ===== */
.erro-canto {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 999;
    width: auto;
    min-width: 250px;
}

.sucesso-canto {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 999;
    width: auto;
    min-width: 250px;
}

/* ===== RESPONSIVO ===== */
@media (max-width: 520px) {
    .wrapper, .controls-container {
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>
</head>

<body>
    <!-- ===== CONTROLES NO TOPO ===== -->
    <?php include 'controls.php'; ?>

    <!-- Mensagem de erro -->
    <?php if($erro): ?>
    <div class="alert alert-danger erro-canto alert-dismissible fade show">
        <?=$erro?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php endif; ?>

    <!-- Mensagem de sucesso -->
    <?php if($sucesso): ?>
    <div class="alert alert-success sucesso-canto alert-dismissible fade show">
        <?=$sucesso?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php endif; ?>

    <!-- FORMULÁRIO -->
    <div class="wrapper">
        <h4 class="text-center mb-4">Cadastro de Usuário</h4>

        <form method="post" id="cadastroForm">
            <!-- Nome Completo -->
            <div class="form-group">
                <label>Nome Completo*</label>
                <input type="text" name="nome" class="form-control" required 
                       minlength="15" maxlength="80" 
                       placeholder="Digite seu nome completo">
            </div>       

            <div class="row">
                <!-- Data de Nascimento -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Data de Nascimento*</label>
                        <input type="date" name="data_nascimento" class="form-control" required>
                    </div>
                </div>
                <!-- Sexo -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Sexo*</label>
                        <select name="sexo" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Nome Materno -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nome Materno*</label>
                        <input type="text" name="nome_materno" class="form-control" required 
                               placeholder="Nome completo da mãe">
                    </div>
                </div>
                <!-- CPF -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>CPF*</label>
                        <input type="text" name="cpf" id="cpf" class="form-control" required 
                               placeholder="000.000.000-00">
                    </div>
                </div>
            </div>

            <!-- E-mail -->
            <div class="form-group">
                <label>E-mail*</label>
                <input type="email" name="email" class="form-control" required 
                       placeholder="seu@email.com">
            </div>

            <div class="row">
                <!-- Telefone Celular -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Telefone Celular*</label>
                        <input type="text" name="telefone_celular" id="celular" 
                               class="form-control" required 
                               placeholder="(+55)XX-XXXXXXXX">
                    </div>
                </div>
                <!-- Telefone Fixo -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Telefone Fixo*</label>
                        <input type="text" name="telefone_fixo" id="fixo" 
                               class="form-control" required 
                               placeholder="(+55)XX-XXXXXXXX">
                    </div>
                </div>
            </div>

            <!-- Endereço Completo -->
            <h5 class="mt-3 text-center">Endereço Completo</h5>
            
            <!-- CEP -->
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>CEP*</label>
                        <input type="text" name="cep" id="cep" class="form-control" required 
                               placeholder="00000-000">
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Logradouro -->
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Logradouro*</label>
                        <input type="text" name="logradouro" id="logradouro" 
                               class="form-control" required>
                    </div>
                </div>
                <!-- Número -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Número*</label>
                        <input type="text" name="numero" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Complemento -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Complemento</label>
                        <input type="text" name="complemento" class="form-control">
                    </div>
                </div>
                <!-- Bairro -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Bairro*</label>
                        <input type="text" name="bairro" id="bairro" 
                               class="form-control" required>
                    </div>
                </div>
                <!-- Cidade -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Cidade*</label>
                        <input type="text" name="cidade" id="cidade" 
                               class="form-control" required>
                    </div>
                </div>
                <!-- Estado -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Estado*</label>
                        <input type="text" name="estado" id="estado" 
                               class="form-control" required maxlength="2">
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Login -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Login* (6 caracteres alfabéticos)</label>
                        <input type="text" name="login" class="form-control" required 
                               minlength="6" maxlength="6" pattern="[A-Za-z]{6}"
                               placeholder="6 letras">
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Senha -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Senha* (8 caracteres alfabéticos)</label>
                        <input type="password" name="senha" id="senha" 
                               class="form-control" required 
                               minlength="8" maxlength="8" pattern="[A-Za-z]{8}"
                               placeholder="8 letras">
                    </div>
                </div>
                <!-- Confirmar Senha -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Confirmar Senha*</label>
                        <input type="password" name="confirmar_senha" 
                               class="form-control" required 
                               minlength="8" maxlength="8" pattern="[A-Za-z]{8}"
                               placeholder="Repita a senha">
                    </div>
                </div>
            </div>

            <!-- Botões -->
<div class="row mt-4">
    <div class="col-md-6">
        <button type="submit" class="btn btn-outline-danger btn-lg btn-block">Enviar</button>
    </div>
    <div class="col-md-6">
        <button type="reset" class="btn btn-outline-danger btn-lg btn-block">Limpar</button>
    </div>
</div>

<!-- Botão Voltar (em outra row) -->
<div class="row mt-4">
    <div class="col-md-12 text-center">
        <a href="login.php" class="btn btn-outline-danger btn-lg btn-block">Voltar para Login</a>
    </div>
</div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#cpf').mask('000.000.000-00');
        $('#celular').mask('(+55)00-000000009');
        $('#fixo').mask('(+55)00-00000000');
        $('#cep').mask('00000-000');
        
        $('#cep').blur(function() {
            var cep = $(this).val().replace(/\D/g, '');
            
            // CORREÇÃO 1: Validar tamanho do CEP antes de chamar a API
            if (cep.length !== 8) {
                alert('CEP inválido. Deve ter 8 dígitos numéricos.');
                return; // Não chama a API se o CEP for inválido
            }
            
            $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function(dados) {
                if (!dados.erro) {
                    $('#logradouro').val(dados.logradouro);
                    $('#bairro').val(dados.bairro);
                    $('#cidade').val(dados.localidade);
                    $('#estado').val(dados.uf);
                } else {
                    alert('CEP não encontrado. Preencha manualmente.');
                }
            }).fail(function() {
                alert('Erro ao buscar CEP. Preencha manualmente.');
            });
        });
    });
    </script>
</body>
</html>