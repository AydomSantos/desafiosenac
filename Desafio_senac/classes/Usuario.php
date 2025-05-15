<?php
require_once __DIR__ . '/../config.php';

class Usuario {
    protected $id;
    protected $nome;
    protected $email;
    protected $senha;
    protected $idioma;
    protected $tema;

    public function __construct($nome, $email, $senha, $idioma = 'pt', $tema = 'claro') {
        $this->id = uniqid();
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = password_hash($senha, PASSWORD_DEFAULT);
        $this->idioma = $idioma;
        $this->tema = $tema;
    }

    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getEmail() { return $this->email; }
    public function getSenha() { return $this->senha; }
    public function getIdioma() { return $this->idioma; }
    public function getTema() { return $this->tema; }

    public function verificarSenha($senha) {
        return password_verify($senha, $this->senha);
    }

    public function salvar() {
        $usuarios = carregarUsuarios();
        $usuarios[$this->id] = [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'senha' => $this->senha,
            'idioma' => $this->idioma,
            'tema' => $this->tema,
            'tipo' => 'usuario'
        ];
        salvarUsuarios($usuarios);
    }
}
?>