<?php
require_once 'config.php';
require_once 'classes/Usuario.php';
require_once 'classes/Administrador.php';
require_once 'classes/Sessao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitiza os inputs
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';

        // Validações básicas
        if (empty($email) || empty($senha)) {
            throw new Exception('Preencha todos os campos!');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido!');
        }

        // Carrega usuários (deve retornar um array)
        $usuarios = carregarUsuarios();
        
        // Verifica se $usuarios é um array antes de iterar
        if (!is_array($usuarios)) {
            throw new Exception('Erro ao carregar usuários cadastrados');
        }

        $usuarioEncontrado = null;

        foreach ($usuarios as $id => $usuario) {
            // Verifica se $usuario é um array e tem a chave 'email'
            if (is_array($usuario) && isset($usuario['email']) && $usuario['email'] === $email) {
                // Verifica se tem todas as chaves necessárias
                $camposRequeridos = ['nome', 'email', 'senha', 'idioma', 'tema', 'tipo'];
                $camposFaltantes = array_diff($camposRequeridos, array_keys($usuario));
                
                if (!empty($camposFaltantes)) {
                    throw new Exception('Dados do usuário incompletos no sistema');
                }

                // Cria o objeto usuário apropriado
                if ($usuario['tipo'] === 'admin') {
                    $usuarioEncontrado = new Administrador(
                        $usuario['nome'],
                        $usuario['email'],
                        $usuario['senha'],
                        $usuario['idioma'],
                        $usuario['tema']
                    );
                } else {
                    $usuarioEncontrado = new Usuario(
                        $usuario['nome'],
                        $usuario['email'],
                        $usuario['senha'],
                        $usuario['idioma'],
                        $usuario['tema']
                    );
                }
                
                $usuarioEncontrado->setId($id);
                break;
            }
        }

        if (!$usuarioEncontrado) {
            // Removido: error_log("Login attempt: User not found for email - " . $email);
            throw new Exception('Usuário não encontrado!');
        }
        
        if (!$usuarioEncontrado->verificarSenha($senha)) {
            // Removido: error_log("Password verification failed for user: " . $usuarioEncontrado->getEmail());
            throw new Exception('Senha incorreta!');
        }

        Sessao::iniciar($usuarioEncontrado);
        redirect('dashboard.php');
    } catch (Exception $e) {
        $erro = $e->getMessage();
        // Mantém o email digitado para mostrar novamente
        $_SESSION['form_data'] = ['email' => $_POST['email'] ?? ''];
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
                        <h2 class="text-center mb-4">Login</h2>
                        
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
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
                                <input type="password" class="form-control" id="senha" name="senha" required>
                                <span class="password-toggle" onclick="togglePassword('senha', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                                <div class="invalid-feedback">
                                    Por favor, insira sua senha.
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2">Entrar</button>
                            
                            <div class="mt-3 text-center">
                                <a href="register.php">Criar uma conta</a>
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