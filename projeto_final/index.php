<?php
require_once __DIR__ . '/conexao.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// ======================================
// 🔒 Verifica login
// ======================================
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
  header("Location: login.php");
  exit;
}

$id = $_SESSION['id_usuario'];
$tipo = $_SESSION['tipo_usuario'] ?? 'usuario'; // 'usuario' ou 'cliente'

// ======================================
// 👤 Busca dados conforme tipo de usuário
// ======================================
if ($tipo === 'usuario') {
  $stmt = $conn->prepare("SELECT nome, funcao FROM usuarios WHERE id = ?");
  $stmt->bind_param("i", $id);
} else {
  $stmt = $conn->prepare("SELECT nome, 'Cliente' AS funcao FROM clientes WHERE id = ?");
  $stmt->bind_param("i", $id);
}

$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
  header("Location: logout.php");
  exit;
}

$nomeUsuario = $user['nome'];
$funcao = $user['funcao'];

// ======================================
// 🛒 Inicializa carrinho (somente clientes)
// ======================================
if ($tipo === 'cliente' && !isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// ======================================
// 🔁 Função de redirecionamento
// ======================================
function go($u)
{
  header("Location: $u");
  exit;
}

// ======================================
// ➕ Adiciona carta ao carrinho (somente cliente)
// ======================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tipo === 'cliente') {
  $action = $_POST['action'] ?? '';
  $pid = (int)($_POST['product_id'] ?? 0);
  $qty = max(1, (int)($_POST['quantity'] ?? 1));

  $st = $GLOBALS['conn']->prepare("SELECT id, nome, estoque FROM cartas WHERE id = ?");
  $st->bind_param('i', $pid);
  $st->execute();
  $p = $st->get_result()->fetch_assoc();

  if (!$p) {
    $_SESSION['flash'] = 'Carta inválida.';
    go($_SERVER['PHP_SELF']);
  }

  if ($p['estoque'] < $qty) {
    $_SESSION['flash'] = "Estoque insuficiente para {$p['nome']}.";
    go($_SERVER['PHP_SELF']);
  }

  $_SESSION['cart'][$pid] = ($_SESSION['cart'][$pid] ?? 0) + $qty;

  if ($action === 'buy_now') {
    go('carrinho.php');
  }

  $_SESSION['flash'] = "Adicionado ao carrinho: {$p['nome']} (x{$qty}).";
  go($_SERVER['PHP_SELF']);
}

// ======================================
// 🔍 Busca e paginação das cartas
// ======================================
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

if ($q !== '') {
  $like = "%{$q}%";
  $c = $conn->prepare("SELECT COUNT(*) total FROM cartas WHERE (codigo LIKE ? OR nome LIKE ? OR tipo LIKE ? OR raridade LIKE ?)");
  $c->bind_param('ssss', $like, $like, $like, $like);
  $c->execute();
  $total = (int)($c->get_result()->fetch_assoc()['total'] ?? 0);

  $s = $conn->prepare("SELECT id, codigo, nome, tipo, raridade, valor, imagem, estoque 
                       FROM cartas
                       WHERE (codigo LIKE ? OR nome LIKE ? OR tipo LIKE ? OR raridade LIKE ?)
                       ORDER BY nome 
                       LIMIT ? OFFSET ?");
  $s->bind_param('ssssii', $like, $like, $like, $like, $limit, $offset);
} else {
  $total = (int)($conn->query("SELECT COUNT(*) total FROM cartas")->fetch_assoc()['total'] ?? 0);
  $s = $conn->prepare("SELECT id, codigo, nome, tipo, raridade, valor, imagem, estoque 
                       FROM cartas
                       ORDER BY nome 
                       LIMIT ? OFFSET ?");
  $s->bind_param('ii', $limit, $offset);
}
$s->execute();
$res = $s->get_result();

$pages = max(1, (int)ceil($total / $limit));
$cartCount = array_sum($_SESSION['cart'] ?? []);

// ======================================
// ✅ Busca lista de clientes (somente admin)
// ======================================
$clientes = [];
if ($funcao === 'Administrador') {
  $cli_res = $conn->query("SELECT id, nome, email, telefone, cpf FROM clientes ORDER BY nome ASC");
  if ($cli_res) {
    $clientes = $cli_res->fetch_all(MYSQLI_ASSOC);
  }
}
?>
<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <title>Loja de Cartas Pokémon</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="./style.css" />
</head>

<body>
  <!-- ======================== CABEÇALHO ======================== -->
  <div class="header">
    <h1>🃏 PokéStore</h1>
    <div class="header-right">
      <span>
        Olá, <?= htmlspecialchars($nomeUsuario) ?>
        <?php if ($tipo === 'usuario'): ?>
          (<?= htmlspecialchars($funcao) ?>)
        <?php endif; ?>
      </span>

      <?php if ($tipo === 'cliente'): ?>
        <a href="carrinho.php">🛒 Carrinho (<?= (int)$cartCount ?>)</a>
        <a href="vendas/listar.php">📜 Histórico de Compras</a>
      <?php endif; ?>
      <?php if ($funcao === 'Administrador'): ?>
        <a href="usuarios/listar.php">👤 Usuários</a>
        <a href="cartas/listar.php">📦 Estoque</a>
        <a href="relatorios/index.php">📊 Relatórios</a>
        <a href="clientes/listar.php">📋 Clientes</a> <!-- NOVO BOTÃO -->
      <?php elseif ($funcao === 'Vendedor'): ?>
        <a href="relatorios/index.php">📈 Relatórios</a>
        <a href="cartas/listar.php">📦 Estoque</a>
        <a href="clientes/listar.php">📋 Clientes</a>
      <?php endif; ?>


      <a href="logout.php">🚪 Sair</a>
    </div>
  </div>

  <!-- ======================== MENSAGEM DE FLASH ======================== -->
  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash"><?= htmlspecialchars($_SESSION['flash']) ?></div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>

  <!-- ======================== BARRA DE BUSCA ======================== -->
  <form class="topbar" method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por código, nome, tipo ou raridade...">
    <button class="btn btn-cart" type="submit">Buscar</button>
  </form>

  <!-- ======================== LISTA DE CARTAS ======================== -->
  <div class="grid">
    <?php while ($row = $res->fetch_assoc()): ?>
      <div class="card">
        <?php if (!empty($row['imagem'])): ?>
          <img class="img" src="<?= htmlspecialchars($row['imagem']) ?>" alt="<?= htmlspecialchars($row['nome']) ?>">
        <?php endif; ?>

        <div>
          <div class="muted">Código: <?= htmlspecialchars($row['codigo']) ?></div>
          <div style="font-weight:700;font-size:1.05rem;"><?= htmlspecialchars($row['nome']) ?></div>
          <div class="muted">
            <span class="pill"><?= htmlspecialchars($row['tipo']) ?></span>
            <span class="pill"><?= htmlspecialchars($row['raridade']) ?></span>
          </div>
        </div>

        <div class="price">R$ <?= number_format((float)$row['valor'], 2, ',', '.') ?></div>
        <div class="muted">Estoque: <?= (int)$row['estoque'] ?></div>

        <?php if ($tipo === 'cliente'): ?>
          <div class="row">
            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display:flex;gap:8px;align-items:center">
              <input type="hidden" name="product_id" value="<?= (int)$row['id'] ?>">
              <input class="qty" type="number" min="1" max="<?= (int)$row['estoque'] ?>" name="quantity" value="1">
              <button class="btn btn-cart" type="submit" name="action" value="add_to_cart">Adicionar</button>
            </form>
            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
              <input type="hidden" name="product_id" value="<?= (int)$row['id'] ?>">
              <input type="hidden" name="quantity" value="1">
              <button class="btn btn-buy" type="submit" name="action" value="buy_now">Comprar agora</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  </div>

  <!-- ======================== PAGINAÇÃO ======================== -->
  <?php if ($pages > 1): ?>
    <div class="pagination">
      <?php for ($p = 1; $p <= $pages; $p++): ?>
        <?php if ($p == $page): ?>
          <span class="active"><?= $p ?></span>
        <?php else: ?>
          <a href="?q=<?= urlencode($q) ?>&page=<?= $p ?>"><?= $p ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  <?php endif; ?>

</body>

</html>