<?php
require_once __DIR__ . '/../config.php'; // Certifique-se que config.php define carregarUsuarios() e salvarUsuarios()

class Usuario {
    protected $id;
    protected $nome;
    protected $email;
    protected $senhaHash; 
    protected $idioma;
    protected $tema;

    public function __construct($nome, $email, $senhaValor, $idioma = 'pt', $tema = 'claro') {
        
        $this->id = null; 
        $this->nome = $nome;
        $this->email = $email;
        $this->senhaHash = $senhaValor; 
        $this->idioma = $idioma;
        $this->tema = $tema;
    }

    // Método estático para criar um NOVO usuário (usado no cadastro)
    public static function criarNovoUsuario($nome, $email, $senhaPlana, $idioma = 'pt', $tema = 'claro') {
        $usuario = new self($nome, $email, password_hash($senhaPlana, PASSWORD_DEFAULT), $idioma, $tema);
        $usuario->id = uniqid('usr_'); // Gerar ID único para novo usuário
        return $usuario;
    }


    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getEmail() { return $this->email; }
    public function getSenhaHash() { return $this->senhaHash; } 
    public function getIdioma() { return $this->idioma; }
    public function getTema() { return $this->tema; }

    public function verificarSenha($senhaPlana) { // Recebe a senha em texto plano do formulário
        return password_verify($senhaPlana, $this->senhaHash); // Compara com o hash armazenado
    }

    public function setId($id) {
        $this->id = $id;
    }

    // Método para definir uma NOVA senha (e hasheá-la)
    public function setSenha(string $senhaPlana) {
        $this->senhaHash = password_hash($senhaPlana, PASSWORD_DEFAULT);
    }

    // Add these new setter methods:
    public function setNome(string $nome) {
        $this->nome = $nome;
    }

    public function setEmail(string $email) {
        // You might want to add email validation here if not done elsewhere
        $this->email = $email;
    }

    public function setIdioma(string $idioma) {
        $this->idioma = $idioma;
    }

    public function setTema(string $tema) {
        $this->tema = $tema;
    }

    public function salvar() {
        if (empty($this->id)) { 
            $this->id = uniqid('usr_');
        }
        $usuarios = carregarUsuarios();
        $usuarios[$this->id] = [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'senha' => $this->senhaHash, 
            'idioma' => $this->idioma,
            'tema' => $this->tema,
            'tipo' => ($this instanceof Administrador) ? 'admin' : 'usuario' 
        ];
        salvarUsuarios($usuarios);
    }
}
?>