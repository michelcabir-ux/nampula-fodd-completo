<?php
/**
 * Página de Registo
 */
$titulo = 'Criar Conta';
require_once 'includes/header.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha    = $_POST['senha'] ?? '';
    $tipo     = $_POST['tipo'] ?? 'cliente';

    if (!in_array($tipo, ['cliente', 'restaurante'])) {
        $tipo = 'cliente';
    }

    if (strlen($nome) < 2) {
        $erro = 'Nome demasiado curto.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erro = 'Este email já está registado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, telefone, senha, tipo) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $telefone, $hash, $tipo]);
            $sucesso = 'Conta criada com sucesso! Já pode fazer login.';
        }
    }
}
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<div class="container">
    <div class="form-container">
        <h2 style="text-align:center;margin-bottom:25px;">
            <i class="fas fa-user-plus" style="color:var(--laranja);"></i> Criar Conta
        </h2>

        <?php if ($erro): ?>
            <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="alert alert-sucesso"><?= htmlspecialchars($sucesso) ?></div>
            <p style="text-align:center;"><a href="<?= url('login.php') ?>" class="btn-primary" style="display:inline-block;width:auto;">Ir para Login</a></p>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label>Nome completo</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" placeholder="84xxxxxxx" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Senha (mín. 6 caracteres)</label>
                    <input type="password" name="senha" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Tipo de conta</label>
                    <select name="tipo">
                        <option value="cliente" <?= ($_POST['tipo'] ?? '') === 'cliente' ? 'selected' : '' ?>>Cliente (quero pedir comida)</option>
                        <option value="restaurante" <?= ($_POST['tipo'] ?? '') === 'restaurante' ? 'selected' : '' ?>>Restaurante (quero vender)</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Criar Conta
                </button>
            </form>
            <p style="text-align:center;margin-top:20px;">
                Já tem conta? <a href="<?= url('login.php') ?>" style="color:var(--laranja);">Entrar</a>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
