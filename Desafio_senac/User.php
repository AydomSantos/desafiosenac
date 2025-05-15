<?php
abstract class User {
    protected $id;
    protected $nome;
    protected $email;
    protected $dataCadastro;
    
    public function __construct($id, $nome, $email) {
        $this->id = $id;
        $this->nome = htmlspecialchars($nome);
        $this->email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $this->dataCadastro = date('Y-m-d H:i:s');
    }
    
    abstract public function getTipo();
    
    public function toArray() {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'tipo' => $this->getTipo(),
            'data_cadastro' => $this->dataCadastro
        ];
    }
}
?>