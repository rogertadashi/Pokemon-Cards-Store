<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../conectado.php';

$ini = $_GET['ini'] ?? date('Y-m-01');
$fim = $_GET['fim'] ?? date('Y-m-d');

$csv = isset($_GET['csv']);

$sql = "SELECT DATE(v.data_venda) dia, COUNT(*) qtd, SUM(v.valor_total) total
        FROM vendas v
        WHERE DATE(v.data_venda) BETWEEN ? AND ?
        GROUP BY DATE(v.data_venda)
        ORDER BY dia";
$st = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($st, "ss", $ini, $fim);
mysqli_stmt_execute($st);
$res = mysqli_stmt_get_result($st);
$rows = [];
while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;

if ($csv) {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=vendas_por_periodo.csv');
  echo "dia;qtd;total\n";
  foreach ($rows as $r) echo "{$r['dia']};{$r['qtd']};{$r['total']}\n";
  exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <title>Relatório - Vendas por período</title>
  <link rel="stylesheet" href="../style_2.css" />
  <h1>Vendas por período</h1>
  <form>
    <input type="date" name="ini" value="<?= htmlspecialchars($ini) ?>">
    <input type="date" name="fim" value="<?= htmlspecialchars($fim) ?>">
    <button>Filtrar</button>
    <a href="?ini=<?= urlencode($ini) ?>&fim=<?= urlencode($fim) ?>&csv=1">Exportar CSV</a>
    <a href="index.php">Voltar</a>
  </form>
  <table>
    <thead>
      <tr>
        <th>Dia</th>
        <th>Qtd vendas</th>
        <th>Total (R$)</th>
      </tr>
    </thead>
    <tbody>
      <?php $soma = 0;
      foreach ($rows as $r): $soma += $r['total']; ?>
        <tr>
          <td><?= htmlspecialchars($r['dia']) ?></td>
          <td><?= (int)$r['qtd'] ?></td>
          <td><?= number_format((float)$r['total'], 2, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p><strong>Total do período: R$ <?= number_format((float)$soma, 2, ',', '.') ?></strong></p>
  </body>

</html>