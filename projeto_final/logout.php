<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔹 Remove todas as variáveis da sessão
$_SESSION = [];

// 🔹 Destroi o cookie de sessão (boa prática adicional)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 🔹 Finaliza completamente a sessão
session_destroy();

// 🔹 Impede cache (para evitar que o usuário volte com o botão "Voltar")
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// 🔹 Redireciona para o login
header("Location: login.php");
exit;
