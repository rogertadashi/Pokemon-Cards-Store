<?php
require_once dirname(__DIR__) . '/conexao.php';
if (!isset($_SESSION)) session_start();

$id = (int)($_GET['id'] ?? 0);
if($id<=0){ http_response_code(400); exit('ID inválido.'); }

$vh=$connect->prepare("
  SELECT v.id, v.data_venda, v.valor_total, v.condicao_pagamento,
         c.nome AS cliente
  FROM vendas v
  JOIN clientes c ON c.id=v.cliente_id
  WHERE v.id=?
");
$vh->bind_param('i',$id);
$vh->execute();
$head=$vh->get_result()->fetch_assoc();
if(!$head){ http_response_code(404); exit('Venda não encontrada.'); }

$vi=$connect->prepare("
  SELECT iv.quantidade, iv.valor_unitario,
         ca.codigo, ca.nome
  FROM itens_venda iv
  JOIN cartas ca ON ca.id=iv.carta_id
  WHERE iv.venda_id=?
  ORDER BY ca.nome
");
$vi->bind_param('i',$id);
$vi->execute();
$items=$vi->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html lang="pt-BR"><head>
<meta charset="utf-8"><title>Venda #<?= (int)$head['id'] ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
 body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;margin:20px;background:#0b0b0b;color:#eaeaea}
 .box{background:#111;border:1px solid #1f1f1f;border-radius:12px;padding:14px;margin-bottom:16px}
 table{width:100%;border-collapse:collapse} th,td{padding:8px;border-bottom:1px solid #1f1f1f} th{text-align:left}
 .muted{color:#bdbdbd}
</style></head><body>
<h1>Venda #<?= (int)$head['id'] ?></h1>
<p><a style="color:#93c5fd" href="../index.php">← Voltar à loja</a></p>

<div class="box">
  <p><b>Cliente:</b> <?= htmlspecialchars($head['cliente']) ?></p>
  <p><b>Data:</b> <?= htmlspecialchars($head['data_venda']) ?></p>
  <p><b>Condição:</b> <?= htmlspecialchars($head['condicao_pagamento']) ?></p>
</div>

<div class="box">
  <table>
    <thead><tr><th>Código</th><th>Nome</th><th>Qtd</th><th>Preço</th><th>Subtotal</th></tr></thead>
    <tbody>
      <?php $total=0.0; foreach($items as $it): $sub=$it['quantidade']*$it['valor_unitario']; $total+=$sub; ?>
        <tr>
          <td><?= htmlspecialchars($it['codigo']) ?></td>
          <td><?= htmlspecialchars($it['nome']) ?></td>
          <td><?= (int)$it['quantidade'] ?></td>
          <td>R$ <?= number_format($it['valor_unitario'],2,',','.') ?></td>
          <td>R$ <?= number_format($sub,2,',','.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p style="text-align:right;margin-top:10px;font-weight:700;">Total: R$ <?= number_format($total,2,',','.') ?></p>
  <p class="muted">* O total exibido deve bater com <code>vendas.valor_total</code>.</p>
</div>
</body></html>
