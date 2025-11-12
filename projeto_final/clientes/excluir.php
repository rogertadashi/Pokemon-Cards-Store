<?php
require_once("../conexao.php");

// =============================
// Verifica ID
// =============================
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die("ID do cliente inválido.");

// =============================
// Exclui cliente
// =============================
$stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: listar.php");
    exit;
} else {
    echo "<p style='color:red;'>Erro ao excluir cliente: " . htmlspecialchars($stmt->error) . "</p>";
    $stmt->close();
}
