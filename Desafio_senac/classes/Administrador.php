<?php
require_once __DIR__ . '/Usuario.php';

class Administrador extends Usuario {
    public function __construct($nome, $email, $senha, $idioma = 'pt', $tema = 'claro') {
        parent::__construct($nome, $email, $senha, $idioma, $tema);
    }

    public function listarUsuarios() {
        $usuarios = carregarUsuarios();
        return array_filter($usuarios, function($usuario) {
            return $usuario['tipo'] === 'usuario';
        });
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
            'tipo' => 'admin'
        ];
        salvarUsuarios($usuarios);
    }
}
?>