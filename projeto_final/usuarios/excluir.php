<?php
require_once("../conexao.php");
require_once("../conectado.php"); // garante que o usuário está logado

// =============================
//  Validação do ID
// =============================
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    die("<p style='color:red'>ID inválido.</p>");
}

// Impede o usuário de deletar a si mesmo
$idUsuarioLogado = $_SESSION['id_usuario'] ?? 0;
if ($id === $idUsuarioLogado) {
    die("<p style='color:orange'>⚠️ Você não pode excluir o próprio usuário logado!</p>");
}

// =============================
//  Verifica se o usuário existe
// =============================
$stmt = mysqli_prepare($conn, "SELECT nome FROM usuarios WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$usuario) {
    die("<p style='color:red'>Usuário não encontrado.</p>");
}

// =============================
//  Exclui o usuário
// =============================
$stmt = mysqli_prepare($conn, "DELETE FROM usuarios WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    echo "<p style='color:lightgreen'>✅ Usuário <strong>" . htmlspecialchars($usuario['nome']) . "</strong> excluído com sucesso!</p>";
    header("Refresh: 2; URL=listar.php"); // redireciona após 2s
} else {
    echo "<p style='color:red'>Erro ao excluir: " . mysqli_error($conn) . "</p>";
}

mysqli_stmt_close($stmt);
