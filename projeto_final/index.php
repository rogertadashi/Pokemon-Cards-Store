<?php
// index.php
require_once __DIR__ . '/conexao.php';
if (!isset($_SESSION)) session_start();

/**
 * (opcional) se quiser bloquear acesso à loja sem login:
 *
 * if (empty($_SESSION['id_usuario'])) {
 *   header('Location: login.php'); // troque para sua tela de login
 *   exit;
 * }
 */

/**
 * Inicia carrinho na sessão:
 * $_SESSION['cart'] = [ carta_id => quantidade ]
 */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * Função para redirecionar rápido
 */
function go($url) {
    header("Location: $url");
    exit;
}

/**
 * 1. TRATAR POST (adicionar ao carrinho / comprar agora)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action']      ?? '';
    $pid    = (int)($_POST['product_id'] ?? 0);
    $qty    = (int)($_POST['quantity']   ?? 1);
    if ($qty <= 0) $qty = 1;

    // validar carta existe
    $stmtCheck = $connect->prepare("SELECT id, nome, valor FROM cartas WHERE id = ?");
    $stmtCheck->bind_param("i", $pid);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $carta = $resultCheck->fetch_assoc();

    if (!$carta) {
        $_SESSION['flash'] = "Carta inválida.";
        go($_SERVER['PHP_SELF']);
    }

    // adiciona ao carrinho
    if (!isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid] = 0;
    }
    $_SESSION['cart'][$pid] += $qty;

    if ($action === 'buy_now') {
        // ir direto para checkout/venda
        // você pode apontar isso depois pra vendas/cadastrar.php
        go('carrinho.php');
    } else {
        $_SESSION['flash'] = "Adicionado ao carrinho: {$carta['nome']} (x{$qty}).";
        go($_SERVER['PHP_SELF']);
    }
}

/**
 * 2. BUSCA / PAGINAÇÃO
 */
$q = trim($_GET['q'] ?? ''); // termo de busca
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$total = 0;
$pages = 1;

/**
 * 2a. Contar total de itens pra paginação
 */
if ($q === '') {
    // sem filtro
    $sqlCount = "SELECT COUNT(*) AS total FROM cartas";
    $stmtCount = $connect->prepare($sqlCount);
    $stmtCount->execute();
} else {
    // com filtro
    $sqlCount = "
        SELECT COUNT(*) AS total
        FROM cartas
        WHERE (codigo LIKE CONCAT('%', ?, '%')
           OR  nome   LIKE CONCAT('%', ?, '%')
           OR  raridade LIKE CONCAT('%', ?, '%'))
    ";
    $stmtCount = $connect->prepare($sqlCount);
    $stmtCount->bind_param("sss", $q, $q, $q);
    $stmtCount->execute();
}

$resultCount = $stmtCount->get_result();
if ($resultCount) {
    $rowTotal = $resultCount->fetch_assoc();
    $total = (int)($rowTotal['total'] ?? 0);
} else {
    $total = 0;
}
$pages = max(1, (int)ceil($total / $limit));

/**
 * 2b. Buscar as cartas pra mostrar na tela
 *
 * Campos usados:
 * id, codigo, nome, tipo, raridade, valor, imagem
 */
if ($q === '') {
    // sem filtro
    $sqlList = "
        SELECT id, codigo, nome, tipo, raridade, valor, imagem
        FROM cartas
        ORDER BY id ASC
        LIMIT ? OFFSET ?
    ";
    $stmtList = $connect->prepare($sqlList);
    $stmtList->bind_param("ii", $limit, $offset);
} else {
    // com filtro
    $sqlList = "
        SELECT id, codigo, nome, tipo, raridade, valor, imagem
        FROM cartas
        WHERE (codigo LIKE CONCAT('%', ?, '%')
           OR  nome   LIKE CONCAT('%', ?, '%')
           OR  raridade LIKE CONCAT('%', ?, '%'))
        ORDER BY id ASC
        LIMIT ? OFFSET ?
    ";
    $stmtList = $connect->prepare($sqlList);
    $stmtList->bind_param("sssii", $q, $q, $q, $limit, $offset);
}

$stmtList->execute();
$res = $stmtList->get_result();

/**
 * contador do carrinho
 */
$cartCount = array_sum($_SESSION['cart']);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Loja de Cartas Pokémon</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<style>
  body {
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
    margin: 20px;
    background: #0b0b0b;
    color: #eaeaea;
  }
  .header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:12px;
  }
  .header-right a {
    color:#93c5fd;
    text-decoration:none;
    margin-left:12px;
    font-size:0.9rem;
  }
  .flash {
    background:#0f172a;
    border:1px solid #1e293b;
    padding:10px 12px;
    border-radius:10px;
    margin-bottom:14px;
    color:#fff;
    font-size:0.9rem;
  }
  .topbar {
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    align-items:center;
    margin-bottom:18px;
  }
  .search-input {
    padding:8px 10px;
    border:1px solid #333;
    background:#121212;
    color:#eee;
    border-radius:8px;
    min-width:260px;
  }
  .btn {
    padding:8px 12px;
    border:0;
    border-radius:10px;
    cursor:pointer;
    font-size:0.9rem;
  }
  .btn-search {
    background:#1f2937;
    color:#fff;
  }
  .btn-cart {
    background:#1f2937;
    color:#fff;
  }
  .btn-buy {
    background:#16a34a;
    color:#fff;
  }
  .grid {
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap:16px;
  }
  .card {
    background:#111;
    border:1px solid #1f1f1f;
    border-radius:16px;
    padding:14px;
    display:flex;
    flex-direction:column;
    gap:10px;
  }
  .muted {
    color:#bdbdbd;
    font-size:12px;
    line-height:1.4;
  }
  .name {
    font-size:1rem;
    font-weight:600;
    color:#fff;
  }
  .price {
    font-weight:600;
    font-size:1rem;
    color:#fff;
  }
  .row {
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    align-items:center;
  }
  .qty {
    width:64px;
    padding:6px 8px;
    border-radius:8px;
    border:1px solid #333;
    background:#121212;
    color:#eee;
    font-size:0.9rem;
  }
  .pokemon-img {
    width:100%;
    max-width:180px;
    border-radius:12px;
    border:1px solid #2a2a2a;
    background:#000;
    object-fit:cover;
  }
  .pagination {
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    margin-top:20px;
  }
  .pagination a,
  .pagination span {
    padding:6px 10px;
    background:#111;
    border:1px solid #1f1f1f;
    color:#ddd;
    border-radius:8px;
    text-decoration:none;
    font-size:0.9rem;
  }
  .active {
    background:#2563eb;
    border-color:#1d4ed8;
    color:#fff !important;
  }
</style>
</head>
<body>

<div class="header">
  <h1 style="font-size:1.1rem;margin:0;color:#fff;">Loja de Cartas Pokémon</h1>
  <div class="header-right">
    <a href="carrinho.php">🛒 Carrinho (<?= (int)$cartCount ?>)</a>
    <a href="conectado.php">Dashboard</a>
    <a href="logout.php">Sair</a>
  </div>
</div>

<?php if (!empty($_SESSION['flash'])): ?>
  <div class="flash"><?= htmlspecialchars($_SESSION['flash']) ?></div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<form class="topbar" method="get">
  <input
    class="search-input"
    type="text"
    name="q"
    value="<?= htmlspecialchars($q) ?>"
    placeholder="Buscar por código, nome ou raridade..."
  >
  <button class="btn btn-search" type="submit">Buscar</button>
</form>

<div class="grid">
<?php while ($row = $res->fetch_assoc()): ?>
  <div class="card">
    <?php if (!empty($row['imagem'])): ?>
      <img class="pokemon-img" src="<?= htmlspecialchars($row['imagem']) ?>"
           alt="<?= htmlspecialchars($row['nome']) ?>">
    <?php endif; ?>

    <div class="muted">
      Código: <?= htmlspecialchars($row['codigo']) ?><br>
      Tipo: <?= htmlspecialchars($row['tipo']) ?> |
      Raridade: <?= htmlspecialchars($row['raridade']) ?>
    </div>

    <div class="name"><?= htmlspecialchars($row['nome']) ?></div>

    <div class="price">
      R$ <?= number_format((float)$row['valor'], 2, ',', '.') ?>
    </div>

    <div class="row">
      <!-- Form: adicionar ao carrinho -->
      <form method="post" style="display:flex; gap:8px; align-items:center;">
        <input type="hidden" name="product_id" value="<?= (int)$row['id'] ?>">
        <input class="qty" type="number" step="1" min="1" name="quantity" value="1">
        <button class="btn btn-cart" type="submit" name="action" value="add_to_cart">
          Adicionar
        </button>
      </form>

      <!-- Form: comprar agora -->
      <form method="post">
        <input type="hidden" name="product_id" value="<?= (int)$row['id'] ?>">
        <input type="hidden" name="quantity" value="1">
        <button class="btn btn-buy" type="submit" name="action" value="buy_now">
          Comprar agora
        </button>
      </form>
    </div>
  </div>
<?php endwhile; ?>
</div>

<?php if ($pages > 1): ?>
  <div class="pagination">
    <?php
    for ($p = 1; $p <= $pages; $p++):
        // manter termo de busca na paginação
        $href = '?page=' . $p;
        if ($q !== '') {
            $href .= '&q=' . urlencode($q);
        }
    ?>
      <?php if ($p === $page): ?>
        <span class="active"><?= $p ?></span>
      <?php else: ?>
        <a href="<?= $href ?>"><?= $p ?></a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
<?php endif; ?>

</body>
</html>
