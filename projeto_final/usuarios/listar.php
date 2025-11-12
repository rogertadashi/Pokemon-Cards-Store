<?php
require_once("../conexao.php");
require_once("../conectado.php");

// =============================
//  Verifica permissão
// =============================
$funcao = $_SESSION['funcao'] ?? 'Cliente';
if ($funcao !== 'Administrador') {
    die("<p style='color:red'>❌ Acesso negado. Apenas administradores podem gerenciar usuários.</p>");
}

// =============================
//  Consulta de usuários
// =============================
$sql = "SELECT id, nome, login, funcao FROM usuarios ORDER BY nome ASC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("<p style='color:red'>Erro ao buscar usuários: " . mysqli_error($conn) . "</p>");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Usuários - Administração</title>
    <style>
        body {
            font-family: system-ui, Arial, sans-serif;
            background: #0b0b0b;
            color: #eaeaea;
            margin: 20px;
        }

        h2 {
            color: #93c5fd;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
            background: #111;
            border: 1px solid #1f1f1f;
            border-radius: 8px;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #222;
        }

        th {
            background: #161616;
            text-align: left;
        }

        a {
            color: #93c5fd;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .btn {
            background: #16a34a;
            color: #fff;
            padding: 6px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn:hover {
            background: #15803d;
        }

        .danger {
            background: #dc2626;
        }

        .danger:hover {
            background: #b91c1c;
        }
    </style>
</head>

<body>
    <h2>👤 Lista de Usuários</h2>
    <a class="btn" href="cadastrar.php">➕ Novo Usuário</a>
    <a class="btn" style="background:#2563eb;margin-left:10px" href="../index.php">🏠 Voltar</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Login</th>
                <th>Função</th>
                <th style="width:150px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= htmlspecialchars($row['login']) ?></td>
                        <td><?= htmlspecialchars($row['funcao']) ?></td>
                        <td>
                            <a href="editar.php?id=<?= (int)$row['id'] ?>">✏️ Editar</a> |
                            <a href="excluir.php?id=<?= (int)$row['id'] ?>"
                                class="danger"
                                onclick="return confirm('Tem certeza que deseja excluir o usuário <?= htmlspecialchars($row['nome']) ?>?');">
                                🗑️ Excluir
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;color:#aaa;">Nenhum usuário cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>