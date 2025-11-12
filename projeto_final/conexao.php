<?php
// Inicia a sessão apenas se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==============================
// ⚙️ Configurações do banco
// ==============================
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "pokestore";

// ==============================
// 🔗 Cria conexão MySQLi
// ==============================
$conn = mysqli_connect($servername, $username, $password, $database);

// Verifica a conexão
if (!$conn) {
    die("❌ Falha na conexão com o banco de dados: " . mysqli_connect_error());
}

// Define charset UTF-8 (para acentuação correta)
mysqli_set_charset($conn, "utf8mb4");

// ==============================
// 🔁 Compatibilidade com scripts antigos
// ==============================
$connect = $conn;
