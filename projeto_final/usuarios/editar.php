<?php
require_once("../conexao.php");

$id = intval($_GET['id'] ?? 0);
$mensagem = "";

// ===============================
//  Busca usuário para edição
// ===============================
$stmt = mysqli_prepare($conn, "SELECT id, nome, login, funcao FROM usuarios WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("<p style='color:red'>Usuário não encontrado!</p>");
}

// ===============================
//  Atualiza dados
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $login = trim($_POST['login']);
    $funcao = $_POST['funcao'];

    if ($nome && $login && $funcao) {
        $check = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE login = ? AND id != ?");
        mysqli_stmt_bind_param($check, "si", $login, $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $mensagem = "<p style='color:orange'>❗ Já existe outro usuário com este login.</p>";
        } else {
            $update = mysqli_prepare($conn, "UPDATE usuarios SET nome = ?, login = ?, funcao = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, "sssi", $nome, $login, $funcao, $id);

            if (mysqli_stmt_execute($update)) {
                $mensagem = "<p style='color:lightgreen'>✅ Dados atualizados com sucesso!</p>";
                header("Refresh: 2; URL=listar.php"); // Redireciona após 2s
            } else {
                $mensagem = "<p style='color:red'>Erro ao atualizar: " . mysqli_error($conn) . "</p>";
            }

            mysqli_stmt_close($update);
        }
        mysqli_stmt_close($check);
    } else {
        $mensagem = "<p style='color:yellow'>⚠️ Preencha todos os campos!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <style>
        body {
            font-family: system-ui, Arial;
            background: #0d0d0d;
            color: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        form {
            background: #1a1a1a;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #000;
            width: 320px;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 6px;
            border: 1px solid #333;
            background: #111;
            color: #fff;
        }

        button {
            background: #16a34a;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }

        button:hover {
            background: #22c55e;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .mensagem {
            text-align: center;
            margin-top: 10px;
        }

        a {
            color: #93c5fd;
            display: block;
            text-align: center;
            margin-top: 10px;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <form method="POST">
        <h2>Editar Usuário</h2>
        <input type="text" name="nome" value="<?= htmlspecialchars($row['nome']) ?>" placeholder="Nome completo" required>
        <input type="text" name="login" value="<?= htmlspecialchars($row['login']) ?>" placeholder="Login" required>
        <select name="funcao" required>
            <option value="Administrador" <?= $row['funcao'] === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
            <option value="Vendedor" <?= $row['funcao'] === 'Vendedor' ? 'selected' : '' ?>>Vendedor</option>
        </select>
        <button type="submit">Salvar Alterações</button>
        <a href="listar.php">← Voltar</a>
    </form>

    <div class="mensagem"><?= $mensagem ?></div>

</body>

</html>