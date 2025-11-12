<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../conectado.php';
$erro = $ok = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome = trim($_POST['nome'] ?? '');
  $contato = trim($_POST['contato'] ?? '');
  $telefone = trim($_POST['telefone'] ?? '');
  $email = trim($_POST['email'] ?? '');
  if ($nome === '') $erro = 'Informe o nome';
  if (!$erro) {
    $st = mysqli_prepare($connect, "INSERT INTO fornecedores(nome,contato,telefone,email) VALUES (?,?,?,?)");
    mysqli_stmt_bind_param($st, "ssss", $nome, $contato, $telefone, $email);
    mysqli_stmt_execute($st);
    header("Location: listar.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <title>Novo Fornecedor</title>
</head>

<body>
  <h1>Novo Fornecedor</h1>
  <?php if ($erro) echo "<p style='color:red'>$erro</p>"; ?>
  <form method="post">
    <label>Nome:</label><input name="nome"><br><br>
    <label>Contato:</label><input name="contato"><br><br>
    <label>Telefone:</label><input name="telefone"><br><br>
    <label>Email:</label><input name="email"><br><br>
    <button>Salvar</button>
    <a href="listar.php">Cancelar</a>
  </form>
</body>

</html>