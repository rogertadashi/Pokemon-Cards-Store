<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/conexao.php";

$erro = '';

if (isset($_POST['btn-entrar'])) {
    $login = trim($_POST['login']);
    $senha = trim($_POST['senha']);

    if (empty($login) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {

        // ============================================
        // 1️⃣ Tenta login como USUÁRIO (Admin/Vendedor)
        // ============================================
        $sql_user = "SELECT * FROM usuarios WHERE login = ?";
        $stmt = mysqli_prepare($connect, $sql_user);
        mysqli_stmt_bind_param($stmt, "s", $login);
        mysqli_stmt_execute($stmt);
        $result_user = mysqli_stmt_get_result($stmt);

        if ($result_user && mysqli_num_rows($result_user) > 0) {
            $usuario = mysqli_fetch_assoc($result_user);

            // Senhas podem ser armazenadas criptografadas OU simples (fase de testes)
            if (password_verify($senha, $usuario['senha']) || $senha === $usuario['senha']) {
                $_SESSION['logado'] = true;
                $_SESSION['tipo_usuario'] = 'usuario';
                $_SESSION['id_usuario'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['funcao'] = $usuario['funcao'];

                header("Location: index.php");
                exit;
            } else {
                $erro = "Senha incorreta!";
            }
        } else {

            // ============================================
            // 2️⃣ Tenta login como CLIENTE (novo modelo)
            // ============================================
            $sql_cli = "SELECT * FROM clientes WHERE login = ? OR email = ? OR cpf = ?";
            $stmt2 = mysqli_prepare($connect, $sql_cli);
            mysqli_stmt_bind_param($stmt2, "sss", $login, $login, $login);
            mysqli_stmt_execute($stmt2);
            $result_cli = mysqli_stmt_get_result($stmt2);

            if ($result_cli && mysqli_num_rows($result_cli) > 0) {
                $cliente = mysqli_fetch_assoc($result_cli);

                // Verifica senha (criptografada ou não)
                $senhaCorreta = password_verify($senha, $cliente['senha']) || $senha === $cliente['senha'];

                if ($senhaCorreta) {
                    $_SESSION['logado'] = true;
                    $_SESSION['tipo_usuario'] = 'cliente';
                    $_SESSION['id_cliente'] = $cliente['id']; // ID real do cliente
                    $_SESSION['usuario_nome'] = $cliente['nome'];
                    $_SESSION['funcao'] = 'Cliente';

                    // 🔹 compatibilidade com páginas que usam id_usuario
                    $_SESSION['id_usuario'] = $cliente['id'];

                    header("Location: index.php");
                    exit;
                } else {
                    $erro = "Senha incorreta!";
                }
            } else {
                $erro = "Usuário ou cliente não encontrado!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Login PokéStore</title>
    <link rel="stylesheet" href="./style_2.css" />
</head>

<body>
    <h1>Login de Acesso</h1>

    <?php if (!empty($erro)): ?>
        <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label>Login (usuário, e-mail ou CPF):</label><br>
        <input type="text" name="login" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <button type="submit" name="btn-entrar">Entrar</button>
    </form>

    <!-- NOVO BOTÃO PARA CADASTRO DE CLIENTE -->
    <p style="margin-top:15px;">
        <a href="clientes/cadastrar.php" style="display:inline-block;padding:10px 20px;background:#2563eb;color:white;border-radius:6px;text-decoration:none;">
            Cadastre-se como Cliente
        </a>
    </p>
    </body>


</html>