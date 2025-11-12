<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../conectado.php';

$sql = "SELECT c.id, c.nome, SUM(iv.quantidade) qtd, SUM(iv.quantidade*iv.valor_unitario) faturamento
        FROM itens_venda iv
        JOIN cartas c ON c.id = iv.carta_id
        GROUP BY c.id, c.nome
        ORDER BY qtd DESC, faturamento DESC";
$res = mysqli_query($connect, $sql);
$rows = [];
while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;

if (isset($_GET['csv'])) {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=produtos_mais_vendidos.csv');
  echo "carta_id;nome;qtd;faturamento\n";
  foreach ($rows as $r) {
    echo $r['id'] . ';"' . str_replace('"', '""', $r['nome']) . '";' . $r['qtd'] . ';' . $r['faturamento'] . "\n";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <title>Relatório - Produtos mais vendidos</title>
  <link rel="stylesheet" href="../style_2.css" />
  <h1>Produtos mais vendidos</h1>
  <p>
    <a href="?csv=1">Exportar CSV</a>
    <a href="index.php">Voltar</a>
  </p>
  <table>
    <thead>
      <tr>
        <th>Carta</th>
        <th>Qtd</th>
        <th>Faturamento (R$)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['nome']) ?></td>
          <td><?= (int)$r['qtd'] ?></td>
          <td><?= number_format((float)$r['faturamento'], 2, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </body>

</html>