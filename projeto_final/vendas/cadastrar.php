<?php
require_once dirname(__DIR__) . '/conexao.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// =======================================
// 🚫 Impede acesso de clientes comuns
// =======================================
$funcao = $_SESSION['tipo_usuario'] ?? 'Cliente';
if (!in_array($funcao, ['Administrador', 'Vendedor'])) {
  $_SESSION['flash'] = 'Apenas administradores e vendedores podem registrar vendas.';
  header('Location: ../index.php');
  exit;
}

// =======================================
// 🛒 Verifica se há itens no carrinho
// =======================================
if (empty($_SESSION['cart'])) {
  header('Location: ../index.php');
  exit;
}

// =======================================
// ⚙️ Configurações iniciais
// =======================================
$condicoes = ['À vista', 'Pix', 'Cartão de crédito', 'Cartão de débito', 'Parcelado'];

$items = $_SESSION['cart'];
$ids = array_map('intval', array_keys($items));
$rows = [];
$total = 0.0;

if ($ids) {
  $in = implode(',', $ids);
  $res = $conn->query("SELECT id, codigo, nome, valor, estoque FROM cartas WHERE id IN ($in)");
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
      'estoque' => (int)$r['estoque'],
      'qtd' => $q,
      'linha' => $linha
    ];
  }
}

// =======================================
// 👥 Seleção de cliente
// =======================================
$clientes = [];
$rc = $conn->query("SELECT id, nome FROM clientes ORDER BY nome");
while ($c = $rc->fetch_assoc()) {
  $clientes[] = $c;
}

$erro = $ok = null;

// =======================================
// 💾 Processamento do formulário
// =======================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $clienteId = (int)($_POST['cliente_id'] ?? 0);
  $condicao = $_POST['condicao_pagamento'] ?? 'À vista';

  if (!in_array($condicao, $condicoes, true)) {
    $condicao = 'À vista';
  }

  if ($clienteId <= 0) {
    $erro = 'Selecione um cliente válido.';
  } elseif (empty($rows)) {
    $erro = 'Carrinho vazio.';
  } else {
    $usuarioId = (int)($_SESSION['id_usuario'] ?? 0);

    $conn->begin_transaction();
    try {
      // 🔹 Cria a venda
      $insVenda = $conn->prepare("
        INSERT INTO vendas (cliente_id, usuario_id, valor_total, condicao_pagamento)
        VALUES (?, ?, ?, ?)
      ");
      $insVenda->bind_param('iids', $clienteId, $usuarioId, $total, $condicao);
      $insVenda->execute();
      $vendaId = $insVenda->insert_id;

      // 🔹 Insere os itens e atualiza estoque
      $insItem = $conn->prepare("
        INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
        VALUES (?, ?, ?, ?)
      ");

      $updEstoque = $conn->prepare("
        UPDATE cartas SET estoque = estoque - ? WHERE id = ? AND estoque >= ?
      ");

      foreach ($rows as $it) {
        if ($it['qtd'] > $it['estoque']) {
          throw new Exception("Estoque insuficiente para '{$it['nome']}'.");
        }

        $insItem->bind_param('iiid', $vendaId, $it['id'], $it['qtd'], $it['valor']);
        $insItem->execute();

        $updEstoque->bind_param('iii', $it['qtd'], $it['id'], $it['qtd']);
        $updEstoque->execute();
      }

      $conn->commit();
      $_SESSION['cart'] = []; // limpa carrinho
      header("Location: visualizar.php?id=" . $vendaId);
      exit;
    } catch (Throwable $e) {
      $conn->rollback();
      $erro = "Erro ao registrar venda: " . $e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <title>Finalizar Venda</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial;
      margin: 20px;
      background: #0b0b0b;
      color: #eaeaea;
    }

    .box {
      background: #111;
      border: 1px solid #1f1f1f;
      border-radius: 12px;
      padding: 14px;
      margin-bottom: 16px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      padding: 8px;
      border-bottom: 1px solid #1f1f1f;
    }

    th {
      text-align: left;
    }

    select,
    button {
      padding: 8px 10px;
      border-radius: 10px;
      border: 0;
    }

    select {
      background: #121212;
      color: #eee;
      border: 1px solid #333;
    }

    .btn {
      background: #16a34a;
      color: #fff;
      cursor: pointer;
    }

    .muted {
      color: #bdbdbd;
    }
  </style>
</head>

<body>
  <h1>Finalizar venda</h1>
  <p><a style="color:#93c5fd" href="../carrinho.php">← Voltar ao carrinho</a></p>

  <?php if ($erro): ?>
    <div class="box" style="border-color:#7f1d1d;background:#1f0b0b;">
      <?= htmlspecialchars($erro) ?>
    </div>
  <?php endif; ?>

  <div class="box">
    <form method="post">
      <label>Cliente<br>
        <select name="cliente_id" required>
          <option value="">Selecione...</option>
          <?php foreach ($clientes as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      &nbsp;&nbsp;
      <label>Condição de pagamento<br>
        <select name="condicao_pagamento">
          <?php foreach ($condicoes as $cp): ?>
            <option><?= htmlspecialchars($cp) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      &nbsp;&nbsp;
      <button class="btn" type="submit">Salvar venda</button>
    </form>
  </div>

  <div class="box">
    <h3>Resumo dos itens</h3>
    <table>
      <thead>
        <tr>
          <th>Código</th>
          <th>Nome</th>
          <th>Preço</th>
          <th>Qtd</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['codigo']) ?></td>
            <td><?= htmlspecialchars($it['nome']) ?></td>
            <td>R$ <?= number_format($it['valor'], 2, ',', '.') ?></td>
            <td><?= (int)$it['qtd'] ?></td>
            <td>R$ <?= number_format($it['linha'], 2, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p style="text-align:right;margin-top:10px;font-weight:700;">
      Total: R$ <?= number_format($total, 2, ',', '.') ?>
    </p>
    <p class="muted">
      Obs.: o estoque é atualizado automaticamente após salvar a venda.
    </p>
  </div>
</body>

</html>