<?php
require_once("../conexao.php");

// =============================
// Verifica ID
// =============================
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die("ID do cliente inválido.");

// =============================
// Busca cliente
// =============================
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) die("Cliente não encontrado.");

// =============================
// Atualiza cliente
// =============================
$mensagem = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $cpf = trim($_POST['cpf']);
    $login = trim($_POST['login']);
    $senha = trim($_POST['senha']); // opcional

    if (!$nome || !$login) {
        $mensagem = "<p style='color:red;'>❌ Nome e login são obrigatórios!</p>";
    } else {
        if ($senha) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE clientes SET nome=?, email=?, telefone=?, cpf=?, login=?, senha=? WHERE id=?");
            $stmt->bind_param("ssssssi", $nome, $email, $telefone, $cpf, $login, $senhaHash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE clientes SET nome=?, email=?, telefone=?, cpf=?, login=? WHERE id=?");
            $stmt->bind_param("sssssi", $nome, $email, $telefone, $cpf, $login, $id);
        }

        if ($stmt->execute()) {
            header("Location: listar.php");
            exit;
        } else {
            $mensagem = "<p style='color:red;'>Erro ao atualizar: " . htmlspecialchars($stmt->error) . "</p>";
        }

        $stmt->close();
    }
}
?>

<h2>Editar Cliente</h2>

<?php if ($mensagem) echo $mensagem; ?>

<form method="POST">
    <input type="text" name="nome" value="<?= htmlspecialchars($row['nome']) ?>" placeholder="Nome" required><br>
    <input type="text" name="login" value="<?= htmlspecialchars($row['login']) ?>" placeholder="Login" required><br>
    <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" placeholder="Email"><br>
    <input type="text" name="telefone" value="<?= htmlspecialchars($row['telefone']) ?>" placeholder="Telefone"><br>
    <input type="text" name="cpf" value="<?= htmlspecialchars($row['cpf']) ?>" placeholder="CPF"><br>
    <input type="password" name="senha" placeholder="Nova senha (opcional)"><br>
    <button type="submit">💾 Salvar Alterações</button>
</form>