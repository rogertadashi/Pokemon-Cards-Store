<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../conectado.php';

$sql = "SELECT v.condicao_pagamento, COUNT(*) qtd, SUM(v.valor_total) total
        FROM vendas v
        GROUP BY v.condicao_pagamento
        ORDER BY total DESC";
$res = mysqli_query($connect, $sql);
$rows = [];
while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;

if (isset($_GET['csv'])) {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=vendas_por_condicao.csv');
  echo "condicao_pagamento;qtd;total\n";
  foreach ($rows as $r) {
    echo '"' . str_replace('"', '""', $r['condicao_pagamento']) . '";' . $r['qtd'] . ';' . $r['total'] . "\n";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <title>Relatório - Vendas por condição</title>
  <link rel="stylesheet" href="../style_2.css" />
  <h1>Vendas por condição de pagamento</h1>
  <p>
    <a href="?csv=1">Exportar CSV</a>
    <a href="index.php">Voltar</a>
  </p>
  <table>
    <thead>
      <tr>
        <th>Condição</th>
        <th>Qtd</th>
        <th>Total (R$)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['condicao_pagamento']) ?></td>
          <td><?= (int)$r['qtd'] ?></td>
          <td><?= number_format((float)$r['total'], 2, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </body>

</html>