<?php
require_once("../conexao.php");

$mensagem = "";
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $cpf = trim($_POST['cpf']);
    $login = trim($_POST['login']);
    $senha = trim($_POST['senha']);

    if (!$nome || !$login || !$senha) {
        $mensagem = "<p style='color:red;'>❌ Nome, login e senha são obrigatórios!</p>";
    } else {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO clientes (nome, email, telefone, cpf, login, senha) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nome, $email, $telefone, $cpf, $login, $senhaHash);

        if ($stmt->execute()) {
            $mensagem = "<p style='color:limegreen;'>✅ Cadastro realizado com sucesso! Redirecionando para o login...</p>";
            $sucesso = true;
        } else {
            $mensagem = "<p style='color:red;'>Erro ao cadastrar: " . htmlspecialchars($stmt->error) . "</p>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Cliente</title>
    <style>
        body {
            font-family: system-ui, Arial;
            background: #0b0b0b;
            color: #eaeaea;
            margin: 20px;
        }

        h2 {
            color: #93c5fd;
        }

        form {
            background: #111;
            padding: 20px;
            border-radius: 10px;
            width: 350px;
            box-shadow: 0 0 10px #000;
        }

        input,
        button {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #333;
            background: #1a1a1a;
            color: #eaeaea;
        }

        input:focus {
            border-color: #2563eb;
            outline: none;
        }

        button {
            background: #16a34a;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #15803d;
        }
    </style>
    <?php if ($sucesso): ?>
        <meta http-equiv="refresh" content="2;url=../login.php">
    <?php endif; ?>
</head>

<body>
    <h2>✨ Cadastro de Cliente</h2>

    <?php if ($mensagem) echo $mensagem; ?>

    <form method="POST">
        <input type="text" name="nome" placeholder="Nome" maxlength="100" required>
        <input type="text" name="login" placeholder="Login" maxlength="50" required>
        <input type="email" name="email" placeholder="Email" maxlength="100">
        <input type="text" name="telefone" placeholder="Telefone" maxlength="20">
        <input type="text" name="cpf" placeholder="CPF" maxlength="14">
        <input type="password" name="senha" placeholder="Senha" maxlength="255" required>
        <button type="submit">💾 Cadastrar</button>
    </form>

    <p style="margin-top:15px;">
        <a href="../login.php" style="color:#60a5fa;text-decoration:none;">⬅ Voltar para o Login</a>
    </p>
</body>

</html>