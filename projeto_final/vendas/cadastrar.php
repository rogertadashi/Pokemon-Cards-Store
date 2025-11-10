<?php
// arquivo: vendas/cadastrar.php
require_once dirname(__DIR__) . '/conexao.php';
if (!isset($_SESSION)) session_start();

// (opcional) exigir login
// if (empty($_SESSION['id_usuario'])) { header('Location: ../index.php'); exit; }

// Carrega clientes e cartas para o formulário (GET e POST)
$clientes = [];
$rc = $connect->query("SELECT id, nome FROM clientes ORDER BY nome");
while ($c = $rc->fetch_assoc()) $clientes[] = $c;

$cartas = [];
$rp = $connect->query("SELECT id, codigo, nome, valor FROM cartas ORDER BY nome");
while ($p = $rp->fetch_assoc()) $cartas[] = $p;

$condicoes = ['À vista','Pix','Cartão de crédito','Cartão de débito','Parcelado'];

$erro = $ok = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $clienteId = (int)($_POST['cliente_id'] ?? 0);
  $condicao  = $_POST['condicao_pagamento'] ?? 'À vista';
  $cartaIds  = $_POST['carta_id'] ?? [];
  $qtds      = $_POST['quantidade'] ?? [];

  if (!in_array($condicao, $condicoes, true)) $condicao = 'À vista';
  if ($clienteId <= 0) $erro = 'Selecione um cliente.';
  if (!$erro && (!is_array($cartaIds) || !is_array($qtds) || count($cartaIds) === 0)) {
    $erro = 'Informe pelo menos um item.';
  }

  // Monta itens válidos (soma itens repetidos)
  $mapQty = []; // carta_id => qtd total
  if (!$erro) {
    for ($i=0; $i < count($cartaIds); $i++) {
      $cid = (int)$cartaIds[$i];
      $qtd = (int)$qtds[$i];
      if ($cid > 0 && $qtd > 0) {
        if (!isset($mapQty[$cid])) $mapQty[$cid] = 0;
        $mapQty[$cid] += $qtd;
      }
    }
    if (!$mapQty) $erro = 'Nenhum item válido informado.';
  }

  // Confere cliente existe
  if (!$erro) {
    $st = $connect->prepare("SELECT 1 FROM clientes WHERE id=?");
    $st->bind_param('i', $clienteId);
    $st->execute();
    if (!$st->get_result()->fetch_row()) $erro = 'Cliente não encontrado.';
  }

  // Calcula total pegando valor da tabela cartas
  $total = 0.0;
  $itens = []; // cada item: ['id'=>carta_id, 'qtd'=>qtd, 'unit'=>valor]
  if (!$erro) {
    $stCarta = $connect->prepare("SELECT valor FROM cartas WHERE id=?");
    foreach ($mapQty as $cid => $qtd) {
      $stCarta->bind_param('i', $cid);
      $stCarta->execute();
      $row = $stCarta->get_result()->fetch_assoc();
      if (!$row) { $erro = "Carta ID {$cid} não encontrada."; break; }
      $unit = (float)$row['valor'];
      $total += $unit * $qtd;
      $itens[] = ['id'=>$cid, 'qtd'=>$qtd, 'unit'=>$unit];
    }
    if (!$erro && $total <= 0) $erro = 'Total da venda não pode ser zero.';
  }

  // Define usuário da venda (sessão ou primeiro usuário do banco)
  $usuarioId = (int)($_SESSION['id_usuario'] ?? 0);
  if (!$erro && $usuarioId <= 0) {
    $ru = $connect->query("SELECT id FROM usuarios ORDER BY id LIMIT 1");
    $usuarioId = (int)($ru->fetch_assoc()['id'] ?? 0);
    if ($usuarioId <= 0) $erro = 'Nenhum usuário cadastrado para atribuir à venda.';
  }

  // Grava venda + itens em transação
  if (!$erro) {
    $connect->begin_transaction();
    try {
      // Cabeçalho
      $insVenda = $connect->prepare("
        INSERT INTO vendas (cliente_id, usuario_id, valor_total, condicao_pagamento)
        VALUES (?, ?, ?, ?)
      ");
      $insVenda->bind_param('iids', $clienteId, $usuarioId, $total, $condicao);
      $insVenda->execute();
      $vendaId = $insVenda->insert_id;

      // Itens
      $insItem = $connect->prepare("
        INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
        VALUES (?, ?, ?, ?)
      ");
      foreach ($itens as $it) {
        $insItem->bind_param('iiid', $vendaId, $it['id'], $it['qtd'], $it['unit']);
        $insItem->execute();
      }

      $connect->commit();
      $ok = "Venda #{$vendaId} salva com sucesso! Total R$ " . number_format($total, 2, ',', '.');

      // (Opcional) limpar POST para novo cadastro
      $_POST = [];
    } catch (Throwable $e) {
      $connect->rollback();
      $erro = "Falha ao salvar: " . $e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Cadastrar Venda</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;margin:20px;background:#0b0b0b;color:#eaeaea}
  .box{background:#111;border:1px solid #1f1f1f;border-radius:12px;padding:14px;margin-bottom:16px}
  .row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
  select,input,button{padding:8px 10px;border-radius:10px;border:0}
  select,input{background:#121212;color:#eee;border:1px solid #333}
  button{cursor:pointer}
  .btn{background:#16a34a;color:#fff}
  .btn-gray{background:#1f2937;color:#fff}
  table{width:100%;border-collapse:collapse;margin-top:8px}
  th,td{padding:8px;border-bottom:1px solid #1f1f1f}
  th{text-align:left}
  .muted{color:#bdbdbd}
  .right{text-align:right}
</style>
<script>
function addItemRow() {
  const tpl = document.getElementById('tpl').content.cloneNode(true);
  document.getElementById('items').appendChild(tpl);
}
function removeRow(btn) {
  const row = btn.closest('tr');
  if (row) row.remove();
}
</script>
</head>
<body>

<h1>Cadastrar venda</h1>
<p><a style="color:#93c5fd" href="../index.php">← Voltar à loja</a></p>

<?php if ($erro): ?>
  <div class="box" style="border-color:#7f1d1d;background:#1f0b0b;"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>
<?php if ($ok): ?>
  <div class="box" style="border-color:#14532d;background:#0b1f14;"><?= $ok ?></div>
<?php endif; ?>

<div class="box">
  <form method="post">
    <div class="row">
      <label>Cliente<br>
        <select name="cliente_id" required>
          <option value="">Selecione...</option>
          <?php foreach ($clientes as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (isset($_POST['cliente_id']) && (int)$_POST['cliente_id']===(int)$c['id'])?'selected':'' ?>>
              <?= htmlspecialchars($c['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Condição de pagamento<br>
        <select name="condicao_pagamento">
          <?php foreach ($condicoes as $cp): ?>
            <option <?= (isset($_POST['condicao_pagamento']) && $_POST['condicao_pagamento']===$cp)?'selected':'' ?>>
              <?= htmlspecialchars($cp) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <button type="button" class="btn-gray" onclick="addItemRow()">+ Adicionar item</button>
      <button type="submit" class="btn">Salvar venda</button>
    </div>

    <table>
      <thead>
        <tr><th style="width:60%">Carta</th><th style="width:20%">Quantidade</th><th class="right" style="width:20%">Ação</th></tr>
      </thead>
      <tbody id="items">
        <!-- linha inicial -->
        <tr>
          <td>
            <select name="carta_id[]" required>
              <option value="">Selecione a carta...</option>
              <?php foreach ($cartas as $p): ?>
                <option value="<?= (int)$p['id'] ?>">
                  <?= htmlspecialchars($p['nome']) ?> (<?= htmlspecialchars($p['codigo']) ?>) — R$ <?= number_format((float)$p['valor'], 2, ',', '.') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><input type="number" name="quantidade[]" min="1" value="1" required></td>
          <td class="right"><button type="button" class="btn-gray" onclick="removeRow(this)">Remover</button></td>
        </tr>
      </tbody>
    </table>

    <!-- template para novas linhas -->
    <template id="tpl">
      <tr>
        <td>
          <select name="carta_id[]" required>
            <option value="">Selecione a carta...</option>
            <?php foreach ($cartas as $p): ?>
              <option value="<?= (int)$p['id'] ?>">
                <?= htmlspecialchars($p['nome']) ?> (<?= htmlspecialchars($p['codigo']) ?>) — R$ <?= number_format((float)$p['valor'], 2, ',', '.') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </td>
        <td><input type="number" name="quantidade[]" min="1" value="1" required></td>
        <td class="right"><button type="button" class="btn-gray" onclick="removeRow(this)">Remover</button></td>
      </tr>
    </template>
  </form>
</div>

<p class="muted">Observação: o valor unitário de cada item é lido da tabela <code>cartas.valor</code> no momento de salvar. O formulário não envia preços.</p>

</body>
</html>
