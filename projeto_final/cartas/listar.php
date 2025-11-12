<?php
require_once("../conexao.php");
require_once("../conectado.php");

// =============================
// Controle de acesso
// =============================
$funcao = $_SESSION['funcao'] ?? 'Cliente';
$permitido = in_array($funcao, ['Administrador', 'Vendedor']);

// =============================
// Busca cartas
// =============================
$result = mysqli_query($conn, "SELECT * FROM cartas ORDER BY nome ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Cartas Pokémon</title>
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

        img {
            max-width: 60px;
            height: auto;
            border-radius: 4px;
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

    <h2>Lista de Cartas Pokémon</h2>
    <?php if ($permitido): ?>
        <p><a href="cadastrar.php">➕ Cadastrar nova carta</a></p>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Imagem</th>
            <th>Código</th>
            <th>Nome</th>
            <th>Tipo</th>
            <th>Raridade</th>
            <th>Valor</th>
            <?php if ($permitido): ?><th>Ações</th><?php endif; ?>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= (int)$row['id'] ?></td>
                <td>
                    <?php if (!empty($row['imagem'])): ?>
                        <img src="<?= htmlspecialchars($row['imagem']) ?>" alt="<?= htmlspecialchars($row['nome']) ?>">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['codigo']) ?></td>
                <td><?= htmlspecialchars($row['nome']) ?></td>
                <td><?= htmlspecialchars($row['tipo']) ?></td>
                <td><?= htmlspecialchars($row['raridade']) ?></td>
                <td>R$ <?= number_format((float)$row['valor'], 2, ',', '.') ?></td>
                <?php if ($permitido): ?>
                    <td>
                        <a class="btn-edit" href="editar.php?id=<?= (int)$row['id'] ?>">Editar</a>
                        <a class="btn-delete" href="excluir.php?id=<?= (int)$row['id'] ?>" onclick="return confirm('Excluir esta carta?');">Excluir</a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endwhile; ?>
    </table>

</body>

</html>