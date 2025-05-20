<?php
session_start();
require_once 'config.php';
require_once 'classes/Usuario.php';


function limpar_input($chave, $filtro = FILTER_SANITIZE_SPECIAL_CHARS) {
    return filter_input(INPUT_POST, $chave, $filtro) ?? '';
}

function manter_valor($chave) {
    return isset($_SESSION['form_data'][$chave]) ? htmlspecialchars($_SESSION['form_data'][$chave]) : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitiza e coleta os dados
        $nome = limpar_input('nome');
        $email = limpar_input('email', FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        $idioma = limpar_input('idioma');
        $tema = limpar_input('tema');

        $_SESSION['form_data'] = $_POST; // Mantém dados em caso de erro

        // Validações
        if (!$nome || !$email || !$senha || !$confirmarSenha) {
            throw new Exception('Todos os campos são obrigatórios.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido.');
        }

        if ($senha !== $confirmarSenha) {
            throw new Exception('As senhas não coincidem.');
        }

        if (strlen($senha) < 8) {
            throw new Exception('A senha deve ter no mínimo 8 caracteres.');
        }

        $usuarios = carregarUsuarios();
        if (!is_array($usuarios)) {
            throw new Exception('Erro ao carregar usuários.');
        }

        foreach ($usuarios as $usuario) {
            if (isset($usuario['email']) && $usuario['email'] === $email) {
                throw new Exception('Email já cadastrado.');
            }
        }

        // Criação e salvamento
        $novoUsuario = Usuario::criarNovoUsuario($nome, $email, $senha, $idioma, $tema);
        $novoUsuario->salvar();

        unset($_SESSION['form_data']);
        $_SESSION['registro_sucesso'] = true;
        header('Location: login.php');
        exit;

    } catch (Exception $e) {
        $erro = $e->getMessage();
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
                        <h2 class="text-center mb-4">Registro</h2>
                        
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <!-- Nome -->
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?= manter_valor('nome') ?>" required>
                                <div class="invalid-feedback">
                                    Por favor, insira seu nome. 
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= manter_valor('email') ?>" required>
                                <div class="invalid-feedback">
                                    Por favor, insira um email válido.
                                </div>
                            </div>
                            
                            <!-- Senha -->
                            <div class="mb-3 password-container">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                                <span class="password-toggle" onclick="togglePassword('senha', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                                <div class="invalid-feedback">
                                    Por favor, insira sua senha.
                                </div>
                            </div>
                            
                            <!-- Confirmar Senha -->
                            <div class="mb-3 password-container">
                                <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                                <span class="password-toggle" onclick="togglePassword('confirmar_senha', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                                <div class="invalid-feedback">
                                    Por favor, confirme sua senha.
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2">Registrar</button>
                            
                            <div class="mt-3 text-center">
                                <a href="login.php">Já tem uma conta? Faça login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
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
                    
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>









