<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../conectado.php';
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die('ID inválido');
$del = mysqli_prepare($connect, "DELETE FROM fornecedores WHERE id=?");
mysqli_stmt_bind_param($del, "i", $id);
mysqli_stmt_execute($del);
header("Location: listar.php");
exit;
