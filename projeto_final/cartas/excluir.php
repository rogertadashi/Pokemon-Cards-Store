<?php
require_once("../conexao.php");
require_once("../conectado.php");

// =============================
// Verifica permissão
// =============================
$funcao = $_SESSION['funcao'] ?? 'Cliente';
if (!in_array($funcao, ['Administrador', 'Vendedor'])) {
    die("<p style='color:red'>❌ Acesso negado. Apenas administradores e vendedores podem excluir cartas.</p>");
}

// =============================
// Verifica ID
// =============================
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("<p style='color:red'>ID da carta inválido.</p>");
}

// =============================
// Exclui carta
// =============================
$stmt = $conn->prepare("DELETE FROM cartas WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: listar.php");
    exit;
} else {
    echo "<p style='color:red'>Erro ao excluir carta: " . htmlspecialchars($stmt->error) . "</p>";
}
