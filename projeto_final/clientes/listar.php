<?php
require_once("../conexao.php");
require_once("../conectado.php");

// =============================
// Permissão (opcional, se quiser restringir edição/exclusão)
// =============================
$funcao = $_SESSION['funcao'] ?? 'Cliente';
$permitido = in_array($funcao, ['Administrador', 'Vendedor']);

// =============================
// Busca clientes
// =============================
$result = mysqli_query($conn, "SELECT * FROM clientes ORDER BY nome ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Clientes</title>
    <style>
        body {
            font-family: system-ui, Arial;
            background: #0b0b0b;
            color: #eaeaea;
            margin: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: #111;
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            border-bottom: 1px solid #222;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #161616;
        }

        a {
            color: #93c5fd;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        button {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: #2563eb;
            color: #fff;
        }

        .btn-delete {
            background: #dc2626;
            color: #fff;
        }
    </style>
</head>

<body>

    <h2>Lista de Clientes</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>CPF</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><?= htmlspecialchars($row['nome']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['telefone']) ?></td>
                <td><?= htmlspecialchars($row['cpf']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

</body>

</html>