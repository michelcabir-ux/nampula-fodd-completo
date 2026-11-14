<?php
/**
 * Página de Login
 */
$titulo = 'Entrar';
require_once 'includes/header.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (fazerLogin($email, $senha)) {
        $tipo = $_SESSION['usuario_tipo'];
        if ($tipo === 'admin') {
            redirect('admin/dashboard.php');
        } elseif ($tipo === 'restaurante') {
            redirect('restaurante/dashboard.php');
        } else {
            redirect('index.php');
        }
    } else {
        $erro = 'Email ou senha incorrectos.';
    }
}
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<div class="container">
    <div class="form-container">
        <h2 style="text-align:center;margin-bottom:25px;">
            <i class="fas fa-sign-in-alt" style="color:var(--laranja);"></i> Entrar
        </h2>

        <?php if ($erro): ?>
            <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="seu@email.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" required placeholder="A sua senha">
            </div>
            <button type="submit" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>

        <p style="text-align:center;margin-top:20px;">
            Ainda não tem conta? <a href="<?= url('registo.php') ?>" style="color:var(--laranja);">Criar conta</a>
        </p>

        <div style="margin-top:30px;padding:15px;background:#f8f8f8;border-radius:8px;font-size:0.85rem;">
            <strong>Contas de demonstração</strong> (senha: <code>password</code>)<br>
            Admin: admin@nampulafodd.co.mz<br>
            Cliente: cliente@teste.com<br>
            Restaurante: maria@restaurante.com
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
