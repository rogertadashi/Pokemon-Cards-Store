<?php
require_once __DIR__ . '/conexao.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

function back()
{
  header("Location: carrinho.php");
  exit;
}

$idUsuario = $_SESSION['id_usuario'] ?? 0;
$tipoUsuario = $_SESSION['tipo_usuario'] ?? 'cliente'; // 'admin', 'vendedor', 'usuario', 'cliente'

// ==========================
// 📦 Ações do carrinho
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $qty = max(0, (int)($_POST['qty'] ?? 0));

    if ($id > 0) {
      if ($qty === 0) unset($_SESSION['cart'][$id]);
      else $_SESSION['cart'][$id] = $qty;
    }
    back();
  }

  if ($action === 'remove') {
    $id = (int)($_POST['id'] ?? 0);
    unset($_SESSION['cart'][$id]);
    back();
  }

  if ($action === 'clear') {
    $_SESSION['cart'] = [];
    back();
  }

  if ($action === 'checkout') {
    // Apenas admin ou vendedor podem registrar vendas
    if ($tipoUsuario === 'Administrador' || $tipoUsuario === 'Vendedor') {
      header('Location: vendas/cadastrar.php');
      exit;
    } else {
      $_SESSION['flash'] = 'Apenas administradores e vendedores podem finalizar compras.';
      back();
    }
  }
}

// ==========================
// 🧾 Monta lista de produtos
// ==========================
$items = $_SESSION['cart'];
$rows = [];
$total = 0.0;

if ($items) {
  $ids = array_map('intval', array_keys($items));
  $in = implode(',', $ids);

  $res = $conn->query("SELECT id, codigo, nome, valor FROM cartas WHERE id IN ($in)");

  while ($r = $res->fetch_assoc()) {
    $q = (int)$items[$r['id']];
    $u = (float)$r['valor'];
    $linha = $q * $u;
    $total += $linha;
    $rows[] = [
      'id' => $r['id'],
      'codigo' => $r['codigo'],
      'nome' => $r['nome'],
      'valor' => $u,
      'qtd' => $q,
      'linha' => $linha
    ];
  }
}
?>
<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <title>Carrinho - PokéStore</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="./style_2.css" />
</head>

<body>
  <h1>Carrinho</h1>
  <p><a style="color:#93c5fd" href="index.php">← Continuar comprando</a></p>

  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash"><?= htmlspecialchars($_SESSION['flash']) ?></div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>

  <?php if (!$rows): ?>
    <p>Seu carrinho está vazio.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Código</th>
          <th>Nome</th>
          <th>Preço</th>
          <th>Qtd</th>
          <th>Subtotal</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['codigo']) ?></td>
            <td><?= htmlspecialchars($it['nome']) ?></td>
            <td>R$ <?= number_format($it['valor'], 2, ',', '.') ?></td>
            <td>
              <form method="post" class="actions" action="carrinho.php">
                <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                <input class="qty" type="number" name="qty" min="0" value="<?= (int)$it['qtd'] ?>">
                <button class="btn btn-gray" type="submit" name="action" value="update">Atualizar</button>
              </form>
            </td>
            <td>R$ <?= number_format($it['linha'], 2, ',', '.') ?></td>
            <td>
              <form method="post" action="carrinho.php">
                <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                <button class="btn btn-red" type="submit" name="action" value="remove">Remover</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="right" style="margin-top:12px;">
      <div style="font-size:1.1rem;font-weight:700;">Total: R$ <?= number_format($total, 2, ',', '.') ?></div>
    </div>

    <div class="row">
      <form method="post" action="carrinho.php">
        <button class="btn btn-red" type="submit" name="action" value="clear">Limpar carrinho</button>
      </form>

      <form method="post" action="index.php" style="margin-left:auto;">
        <button class="btn btn-green" type="submit" name="action" value="checkout">Finalizar compra</button>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>