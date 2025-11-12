<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../conectado.php';

// =============================
//  Controle de acesso
// =============================
$funcao = $_SESSION['funcao'] ?? 'Cliente';
$idClienteLogado = $_SESSION['id_cliente'] ?? 0;

// =============================
//  Filtro de busca
// =============================
$q = trim($_GET['q'] ?? '');
$where = "";
$params = [];
$types = "";

if ($funcao === 'Cliente') {
  // Cliente vê apenas suas vendas
  $where = "WHERE v.cliente_id = ?";
  $params[] = $idClienteLogado;
  $types .= "i";

  if ($q !== '') {
    $where .= " AND v.condicao_pagamento LIKE ?";
    $params[] = "%$q%";
    $types .= "s";
  }
} else {
  // Admin / vendedor pode filtrar por nome do cliente ou condição de pagamento
  if ($q !== '') {
    $where = "WHERE c.nome LIKE ? OR v.condicao_pagamento LIKE ?";
    $like = "%$q%";
    $params = [$like, $like];
    $types = "ss";
  }
}

// =============================
//  Query principal
// =============================
$sql = "SELECT v.id, v.data_venda, v.valor_total, v.condicao_pagamento,
               c.nome AS cliente
        FROM vendas v
        JOIN clientes c ON c.id = v.cliente_id
        $where
        ORDER BY v.data_venda DESC";

$stmt = mysqli_prepare($connect, $sql);
if (!$stmt) {
  die("Erro ao preparar SQL: " . mysqli_error($connect) . "<br><pre>$sql</pre>");
}

if (!empty($params)) {
  mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <title><?= $funcao === 'Cliente' ? 'Minhas Compras' : 'Vendas' ?></title>
  <style>
    body {
      font-family: system-ui, Arial;
      background: #0b0b0b;
      color: #eaeaea;
      margin: 20px;
    }

    table {
      border-collapse: collapse;
      width: 100%;
      background: #111;
      border: 1px solid #1f1f1f;
      border-radius: 8px;
    }

    th,
    td {
      border-bottom: 1px solid #222;
      padding: 10px;
    }

    th {
      text-align: left;
      background: #161616;
    }

    input,
    button {
      padding: 8px;
      border-radius: 8px;
      border: 1px solid #333;
      background: #121212;
      color: #eee;
    }

    button {
      background: #16a34a;
      color: #fff;
      cursor: pointer;
      border: none;
    }

    a {
      color: #93c5fd;
      text-decoration: none;
    }
  </style>
</head>

<body>
  <h1><?= $funcao === 'Cliente' ? 'Minhas Compras' : 'Vendas' ?></h1>

  <form method="get" style="margin-bottom:12px">
    <input type="text" name="q" placeholder="<?= $funcao === 'Cliente' ? 'Filtrar por condição' : 'Cliente ou condição' ?>" value="<?= htmlspecialchars($q) ?>">
    <button>Filtrar</button>
    <a href="../index.php">Voltar</a>
  </form>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Data</th>
        <?php if ($funcao !== 'Cliente'): ?>
          <th>Cliente</th>
        <?php endif; ?>
        <th>Condição</th>
        <th>Total</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($res)): ?>
        <tr>
          <td><?= (int)$row['id'] ?></td>
          <td><?= htmlspecialchars($row['data_venda']) ?></td>
          <?php if ($funcao !== 'Cliente'): ?>
            <td><?= htmlspecialchars($row['cliente']) ?></td>
          <?php endif; ?>
          <td><?= htmlspecialchars($row['condicao_pagamento']) ?></td>
          <td>R$ <?= number_format((float)$row['valor_total'], 2, ',', '.') ?></td>
          <td>
            <a href="visualizar.php?id=<?= (int)$row['id'] ?>">Ver</a>
            <?php if ($funcao !== 'Cliente'): ?>
              | <a href="editar.php?id=<?= (int)$row['id'] ?>">Editar</a>
              | <a href="excluir.php?id=<?= (int)$row['id'] ?>" onclick="return confirm('Excluir esta venda? Isso apagará os itens.');">Excluir</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>

</html>