<?php
require_once 'config.php';
require_once 'classes/Usuario.php';

// Inicia a sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitiza os inputs
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        $idioma = $_POST['idioma'] ?? 'pt';
        $tema = $_POST['tema'] ?? 'claro';

        // Validações
        if (empty($nome) || empty($email) || empty($senha) || empty($confirmarSenha)) {
            throw new Exception('Todos os campos são obrigatórios!');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido!');
        }

        if ($senha !== $confirmarSenha) {
            throw new Exception('As senhas não coincidem!');
        }

        if (strlen($senha) < 8) {
            throw new Exception('A senha deve ter no mínimo 8 caracteres!');
        }

        // Carrega usuários (supondo que carregarUsuarios() retorne um array)
        $usuarios = carregarUsuarios();
        
        // Verifica se $usuarios é um array antes de iterar
        if (!is_array($usuarios)) {
            throw new Exception('Erro ao carregar usuários cadastrados');
        }

        // Verifica se email já existe
        foreach ($usuarios as $usuario) {
            if (is_array($usuario) && isset($usuario['email']) && $usuario['email'] === $email) {
                throw new Exception('Email já cadastrado!');
            }
        }

        // Cria e salva o novo usuário CORRIGIDO
        // Use o método estático para garantir que a senha seja hasheada
        $novoUsuario = Usuario::criarNovoUsuario($nome, $email, $senha, $idioma, $tema);
        $novoUsuario->salvar();

        $_SESSION['registro_sucesso'] = true;
        redirect('login.php');
    } catch (Exception $e) {
        $erro = $e->getMessage();
        // Mantém os valores do formulário para mostrar novamente
        $_SESSION['form_data'] = $_POST;
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        .password-container {
            position: relative;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Criar Conta</h2>
                        
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['registro_sucesso'])): ?>
                            <div class="alert alert-success">Registro realizado com sucesso!</div>
                            <?php unset($_SESSION['registro_sucesso']); ?>
                        <?php endif; ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <!-- Nome -->
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?= isset($_SESSION['form_data']['nome']) ? htmlspecialchars($_SESSION['form_data']['nome']) : '' ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor, insira seu nome.
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : '' ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor, insira um email válido.
                                </div>
                            </div>
                            
                            <!-- Senha -->
                            <div class="mb-3 password-container">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" 
                                       minlength="8" required>
                                <span class="password-toggle" onclick="togglePassword('senha', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                                <div class="invalid-feedback">
                                    A senha deve ter pelo menos 8 caracteres.
                                </div>
                            </div>
                            
                            <!-- Confirmar Senha -->
                            <div class="mb-3 password-container">
                                <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                                <input type="password" class="form-control" id="confirmar_senha" 
                                       name="confirmar_senha" required>
                                <span class="password-toggle" onclick="togglePassword('confirmar_senha', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                                <div class="invalid-feedback">
                                    As senhas devem coincidir.
                                </div>
                            </div>
                            
                            <!-- Idioma -->
                            <div class="mb-3">
                                <label for="idioma" class="form-label">Idioma</label>
                                <select class="form-select" id="idioma" name="idioma" required>
                                    <option value="pt" <?= (isset($_SESSION['form_data']['idioma']) && $_SESSION['form_data']['idioma'] === 'pt') ? 'selected' : '' ?>>Português</option>
                                    <option value="en" <?= (isset($_SESSION['form_data']['idioma']) && $_SESSION['form_data']['idioma'] === 'en') ? 'selected' : '' ?>>Inglês</option>
                                </select>
                            </div>
                            
                            <!-- Tema -->
                            <div class="mb-4">
                                <label for="tema" class="form-label">Tema</label>
                                <select class="form-select" id="tema" name="tema" required>
                                    <option value="claro" <?= (isset($_SESSION['form_data']['tema']) && $_SESSION['form_data']['tema'] === 'claro') ? 'selected' : '' ?>>Claro</option>
                                    <option value="escuro" <?= (isset($_SESSION['form_data']['tema']) && $_SESSION['form_data']['tema'] === 'escuro') ? 'selected' : '' ?>>Escuro</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2">Registrar</button>
                            
                            <div class="text-center mt-3">
                                Já tem uma conta? <a href="login.php">Faça login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mostrar/ocultar senha
        function togglePassword(fieldId, toggleElement) {
            const field = document.getElementById(fieldId);
            const icon = toggleElement.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                field.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        }
        
        // Validação do formulário
        (function() {
            'use strict';
            
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    // Validação personalizada para confirmar senha
                    const senha = document.getElementById('senha');
                    const confirmarSenha = document.getElementById('confirmar_senha');
                    
                    if (senha.value !== confirmarSenha.value) {
                        confirmarSenha.setCustomValidity('As senhas não coincidem');
                        confirmarSenha.classList.add('is-invalid');
                    } else {
                        confirmarSenha.setCustomValidity('');
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
<?php
// Limpa os dados do formulário da sessão após mostrar
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
?>