<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../conectado.php';

$sql = "SELECT DATE(data_venda) dia, COUNT(*) qtd, SUM(valor_total) total, AVG(valor_total) ticket_medio
        FROM vendas
        GROUP BY DATE(data_venda)
        ORDER BY dia DESC";
$res = mysqli_query($connect, $sql);
$rows = [];
while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;

if (isset($_GET['csv'])) {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=ticket_medio_diario.csv');
  echo "dia;qtd;total;ticket_medio\n";
  foreach ($rows as $r) echo "{$r['dia']};{$r['qtd']};{$r['total']};{$r['ticket_medio']}\n";
  exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <title>Relatório - Ticket médio diário</title>
  <link rel="stylesheet" href="../style_2.css" />
  <h1>Ticket médio por dia</h1>
  <p>
    <a href="?csv=1">Exportar CSV</a>
    <a href="index.php">Voltar</a>
  </p>
  <table>
    <thead>
      <tr>
        <th>Dia</th>
        <th>Qtd</th>
        <th>Total (R$)</th>
        <th>Ticket médio (R$)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['dia']) ?></td>
          <td><?= (int)$r['qtd'] ?></td>
          <td><?= number_format((float)$r['total'], 2, ',', '.') ?></td>
          <td><?= number_format((float)$r['ticket_medio'], 2, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </body>

</html>