<?php
require_once("../conexao.php");
require_once("../conectado.php");

// =============================
//  Verifica permissão
// =============================
$funcao = $_SESSION['funcao'] ?? 'Cliente';
if (!in_array($funcao, ['Administrador', 'Vendedor'])) {
    die("<p style='color:red'>❌ Acesso negado. Apenas administradores e vendedores podem cadastrar cartas.</p>");
}

// =============================
//  Processa o cadastro
// =============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codigo = trim($_POST['codigo']);
    $nome = trim($_POST['nome']);
    $imagem = trim($_POST['imagem']); // Novo campo
    $tipo = $_POST['tipo'];
    $raridade = $_POST['raridade'];
    $valor = str_replace(',', '.', $_POST['valor']); // permite 12,50 ou 12.50

    $stmt = $conn->prepare("INSERT INTO cartas (codigo, nome, imagem, tipo, raridade, valor) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssd", $codigo, $nome, $imagem, $tipo, $raridade, $valor);

    if ($stmt->execute()) {
        $mensagem = "<p style='color:limegreen;'>✅ Carta cadastrada com sucesso!</p>";
    } else {
        $mensagem = "<p style='color:red;'>Erro ao cadastrar: " . htmlspecialchars($stmt->error) . "</p>";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Carta</title>
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

        form {
            background: #111;
            padding: 20px;
            border-radius: 10px;
            width: 350px;
            box-shadow: 0 0 10px #000;
        }

        input,
        select,
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

        input:focus,
        select:focus {
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

        .btn-voltar {
            background: #2563eb;
            text-decoration: none;
            color: white;
            padding: 8px 10px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .btn-voltar:hover {
            background: #1d4ed8;
        }
    </style>
</head>

<body>
    <h2>✨ Cadastrar Nova Carta</h2>
    <a class="btn-voltar" href="listar.php">⬅ Voltar para Lista</a>

    <?php if (!empty($mensagem)) echo $mensagem; ?>

    <form method="POST">
        <input type="text" name="codigo" placeholder="Código" maxlength="20" required>
        <input type="text" name="nome" placeholder="Nome da Carta" maxlength="100" required>
        <input type="text" name="imagem" placeholder="URL da Imagem (opcional)">

        <label for="tipo">Tipo:</label>
        <select name="tipo" id="tipo" required>
            <option value="">-- Selecione --</option>
            <option>Fogo</option>
            <option>Água</option>
            <option>Planta</option>
            <option>Elétrico</option>
            <option>Psíquico</option>
            <option>Outros</option>
        </select>

        <label for="raridade">Raridade:</label>
        <select name="raridade" id="raridade" required>
            <option value="">-- Selecione --</option>
            <option>Comum</option>
            <option>Rara</option>
            <option>Ultra Rara</option>
            <option>Lendária</option>
        </select>

        <input type="number" name="valor" step="0.01" placeholder="Valor (R$)" required>

        <button type="submit">💾 Cadastrar</button>
    </form>
</body>

</html>