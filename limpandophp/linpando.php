<?php
function limpar($dado) {
    return htmlspecialchars(strip_tags(trim($dado)), ENT_QUOTES, 'UTF-8');
}

$sexo_opcoes = ['m' => 'Masculino', 'e' => 'Feminino', 'x' => 'Não informado'];

$nome      = isset($_GET['nome']) ? limpar($_GET['nome']) : '';
$sobrenome = isset($_GET['sobrenome']) ? limpar($_GET['sobrenome']) : '';
$email     = filter_var($_GET['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
$telefone  = preg_replace('/\D/', '', $_GET['telefone'] ?? '');
$cep       = preg_replace('/\D/', '', $_GET['cep'] ?? '');
$sexo      = limpar($_GET['sexo'] ?? '');
$newsletter = isset($_GET['newsletter']) ? 'Sim' : 'Não';

$sexo_legivel = $sexo_opcoes[$sexo] ?? 'Não informado';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro</title>
  <style>
    body {
      font-family: sans-serif;
      background: linear-gradient(to top, #ff6600, #ffcc00);
      margin: 0; padding: 0;
    }
    .container {
      max-width: 500px;
      margin: 30px auto;
      padding: 20px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 { text-align: center; color: #007bff; }
    label { display: block; margin: 10px 0 5px; }
    input[type="text"], input[type="email"], input[type="submit"] {
      width: 100%; padding: 10px; margin-bottom: 10px;
      border: 1px solid #ccc; border-radius: 5px;
    }
    .group { margin-bottom: 10px; }
    input[type="submit"] {
      background: #007bff; color: white; font-weight: bold; cursor: pointer;
    }
    input[type="submit"]:hover {
      background: #0056b3;
    }
    .resultado li {
      background: #f2f2f2; margin: 5px 0; padding: 8px; border-radius: 4px;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>Cadastro</h2>
  <form method="get">
    <label>Nome:</label>
    <input type="text" name="nome" required pattern="[A-Za-zÀ-ÿ\s]+">

    <label>Sobrenome:</label>
    <input type="text" name="sobrenome" required pattern="[A-Za-zÀ-ÿ\s]+">

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Telefone:</label>
    <input type="text" name="telefone" required pattern="\d+">

    <label>CEP:</label>
    <input type="text" name="cep" required pattern="\d+">

    <label>Sexo:</label>
    <div class="group">
      <label><input type="radio" name="sexo" value="m" required> Masculino</label>
      <label><input type="radio" name="sexo" value="e"> Feminino</label>
      <label><input type="radio" name="sexo" value="x"> Não informar</label>
    </div>

    <label><input type="checkbox" name="newsletter"> Receber newsletter</label>

    <input type="submit" value="Enviar">
  </form>

  <?php if ($_GET && $nome && $sobrenome && $email): ?>
    <ul class="resultado">
      <li><strong>Nome:</strong> <?= $nome ?></li>
      <li><strong>Sobrenome:</strong> <?= $sobrenome ?></li>
      <li><strong>Email:</strong> <?= $email ?></li>
      <li><strong>Telefone:</strong> <?= $telefone ?></li>
      <li><strong>CEP:</strong> <?= $cep ?></li>
      <li><strong>Sexo:</strong> <?= $sexo_legivel ?></li>
      <li><strong>Newsletter:</strong> <?= $newsletter ?></li>
    </ul>
  <?php endif; ?>
</div>
</body>
</html>
