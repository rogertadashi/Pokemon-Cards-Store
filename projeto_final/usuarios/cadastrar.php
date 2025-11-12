<?php
require_once("../conexao.php");

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome']);
    $login = trim($_POST['login']);
    $senha = $_POST['senha'];
    $funcao = $_POST['funcao'];

    if ($nome && $login && $senha && $funcao) {

        // Verifica se login já existe
        $check = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE login = ?");
        mysqli_stmt_bind_param($check, "s", $login);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $mensagem = "<p style='color:orange'>❗ Este login já está em uso.</p>";
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn, "INSERT INTO usuarios (nome, login, senha, funcao) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $nome, $login, $senhaHash, $funcao);

            if (mysqli_stmt_execute($stmt)) {
                $mensagem = "<p style='color:lightgreen'>✅ Usuário cadastrado com sucesso!</p>";
            } else {
                $mensagem = "<p style='color:red'>Erro ao cadastrar: " . mysqli_error($conn) . "</p>";
            }
            mysqli_stmt_close($stmt);
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
    <title>Cadastrar Usuário</title>
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
            width: 300px;
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
        <h2>Cadastro de Usuário</h2>
        <input type="text" name="nome" placeholder="Nome completo" required>
        <input type="text" name="login" placeholder="Login" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <select name="funcao" required>
            <option value="">Selecione a função</option>
            <option value="Administrador">Administrador</option>
            <option value="Vendedor">Vendedor</option>
        </select>
        <button type="submit">Cadastrar</button>
        <a href="../index.php">← Voltar</a>
    </form>

    <div class="mensagem"><?= $mensagem ?></div>

</body>

</html>