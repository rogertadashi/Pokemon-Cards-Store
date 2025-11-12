<?php
require_once("../conexao.php");
require_once("../conectado.php");

// =============================
// Verifica permissão
// =============================
$funcao = $_SESSION['funcao'] ?? 'Cliente';
if (!in_array($funcao, ['Administrador', 'Vendedor'])) {
    die("<p style='color:red'>❌ Acesso negado. Apenas administradores e vendedores podem editar cartas.</p>");
}

// =============================
// Verifica ID
// =============================
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID da carta inválido.");

// =============================
// Busca carta
// =============================
$stmt = $conn->prepare("SELECT * FROM cartas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) die("Carta não encontrada.");
$stmt->close();

// =============================
// Atualiza carta
// =============================
$mensagem = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $imagem = trim($_POST['imagem']); // novo campo
    $tipo = $_POST['tipo'];
    $raridade = $_POST['raridade'];
    $valor = str_replace(',', '.', $_POST['valor']);
    $estoque = (int)$_POST['estoque'];

    $stmt = $conn->prepare("UPDATE cartas SET nome=?, imagem=?, tipo=?, raridade=?, valor=?, estoque=? WHERE id=?");
    $stmt->bind_param("ssssdis", $nome, $imagem, $tipo, $raridade, $valor, $estoque, $id);

    if ($stmt->execute()) {
        $mensagem = "<p style='color:limegreen;'>✅ Carta atualizada com sucesso!</p>";
        // Atualiza $row para exibir valores atualizados no formulário
        $row['nome'] = $nome;
        $row['imagem'] = $imagem;
        $row['tipo'] = $tipo;
        $row['raridade'] = $raridade;
        $row['valor'] = $valor;
        $row['estoque'] = $estoque;
    } else {
        $mensagem = "<p style='color:red;'>Erro ao atualizar: " . htmlspecialchars($stmt->error) . "</p>";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Editar Carta Pokémon</title>
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
            width: 400px;
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

        a {
            color: #93c5fd;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        img.preview {
            max-width: 150px;
            margin: 10px 0;
            border: 1px solid #333;
            border-radius: 6px;
        }
    </style>
</head>

<body>
    <h2>Editar Carta Pokémon</h2>

    <a href="listar.php">← Voltar à lista de cartas</a>

    <?php if ($mensagem) echo $mensagem; ?>

    <form method="POST">
        <label>Nome:</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($row['nome']) ?>" required>

        <label>URL da Imagem:</label>
        <input type="text" name="imagem" value="<?= htmlspecialchars($row['imagem'] ?? '') ?>" placeholder="URL da imagem">
        <?php if (!empty($row['imagem'])): ?>
            <img class="preview" src="<?= htmlspecialchars($row['imagem']) ?>" alt="Imagem da carta">
        <?php endif; ?>

        <label>Tipo:</label>
        <select name="tipo" required>
            <?php
            $tipos = ['Fogo', 'Água', 'Planta', 'Elétrico', 'Psíquico', 'Outros'];
            foreach ($tipos as $t) {
                $sel = $row['tipo'] === $t ? 'selected' : '';
                echo "<option value='$t' $sel>$t</option>";
            }
            ?>
        </select>

        <label>Raridade:</label>
        <select name="raridade" required>
            <?php
            $raridades = ['Comum', 'Rara', 'Ultra Rara', 'Lendária'];
            foreach ($raridades as $r) {
                $sel = $row['raridade'] === $r ? 'selected' : '';
                echo "<option value='$r' $sel>$r</option>";
            }
            ?>
        </select>

        <label>Valor (R$):</label>
        <input type="number" step="0.01" name="valor" value="<?= htmlspecialchars($row['valor']) ?>" required>

        <label>Estoque:</label>
        <input type="number" name="estoque" value="<?= (int)$row['estoque'] ?>" required>

        <button type="submit">💾 Salvar Alterações</button>
    </form>
</body>

</html>