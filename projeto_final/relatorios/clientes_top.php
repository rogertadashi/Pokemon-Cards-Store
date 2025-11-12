<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../conectado.php';

$sql = "SELECT c.id, c.nome, COUNT(v.id) qtd_vendas, SUM(v.valor_total) gasto
        FROM vendas v
        JOIN clientes c ON c.id = v.cliente_id
        GROUP BY c.id, c.nome
        ORDER BY gasto DESC";
$res = mysqli_query($connect, $sql);
$rows = [];
while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;

if (isset($_GET['csv'])) {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=clientes_top.csv');
  foreach ($rows as $r) {
    echo $r['id'] . ';"' . str_replace('"', '""', $r['nome']) . '";' . $r['qtd_vendas'] . ';' . $r['gasto'] . "\n";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <title>Relatório - Clientes top</title>
  <link rel="stylesheet" href="../style_2.css" />
  <h1>Clientes que mais compram</h1>
  <p>
    <a href="?csv=1">Exportar CSV</a>
    <a href="index.php">Voltar</a>
  </p>
  <table>
    <thead>
      <tr>
        <th>Cliente</th>
        <th>Qtd vendas</th>
        <th>Gasto (R$)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['nome']) ?></td>
          <td><?= (int)$r['qtd_vendas'] ?></td>
          <td><?= number_format((float)$r['gasto'], 2, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </body>

</html>