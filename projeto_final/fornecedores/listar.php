<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../conectado.php';

$res = mysqli_query($connect, "SELECT * FROM fornecedores ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <title>Fornecedores - Listar</title>
  <style>
    body {
      font-family: system-ui;
      margin: 20px
    }

    table {
      width: 100%;
      border-collapse: collapse
    }

    th,
    td {
      border: 1px solid #ddd;
      padding: 8px
    }
  </style>
</head>

<body>
  <h1>Fornecedores</h1>
  <p><a href="cadastrar.php">Novo</a> | <a href="../index.php">Início</a></p>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Nome</th>
        <th>Contato</th>
        <th>Telefone</th>
        <th>Email</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($f = mysqli_fetch_assoc($res)): ?>
        <tr>
          <td><?= (int)$f['id'] ?></td>
          <td><?= htmlspecialchars($f['nome']) ?></td>
          <td><?= htmlspecialchars($f['contato']) ?></td>
          <td><?= htmlspecialchars($f['telefone']) ?></td>
          <td><?= htmlspecialchars($f['email']) ?></td>
          <td>
            <a href="editar.php?id=<?= (int)$f['id'] ?>">Editar</a> |
            <a href="excluir.php?id=<?= (int)$f['id'] ?>" onclick="return confirm('Excluir fornecedor?');">Excluir</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>

</html>