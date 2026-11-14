<?php
/**
 * Carteira Virtual do Cliente (M-Pesa / E-Mola simulado)
 */
$titulo = 'Minha Carteira';
require_once '../includes/header.php';
proteger(['cliente']);

$user = utilizadorAtual();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor = floatval($_POST['valor'] ?? 0);
    $metodo = $_POST['metodo'] ?? 'mpesa';

    if ($valor < 50) {
        $msg = '<div class="alert alert-erro">Valor mínimo: 50 MT</div>';
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET saldo_carteira = saldo_carteira + ? WHERE id = ?");
        $stmt->execute([$valor, $user['id']]);

        $desc = 'Depósito via ' . strtoupper($metodo) . ' (simulado)';
        $stmt = $pdo->prepare("INSERT INTO carteira (usuario_id, tipo, valor, descricao) VALUES (?, 'deposito', ?, ?)");
        $stmt->execute([$user['id'], $valor, $desc]);

        $msg = '<div class="alert alert-sucesso">Saldo carregado com sucesso! +' . number_format($valor, 2) . ' MT</div>';
        $user = utilizadorAtual(); // actualiza saldo
    }
}

$stmt = $pdo->prepare("SELECT * FROM carteira WHERE usuario_id = ? ORDER BY criado_em DESC LIMIT 30");
$stmt->execute([$user['id']]);
$historico = $stmt->fetchAll();
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<div class="container" style="padding-top:30px;">
    <h1 class="secao-titulo"><i class="fas fa-wallet"></i> Minha Carteira</h1>
    <?= $msg ?>

    <div class="stat-card" style="max-width:400px;margin-bottom:30px;">
        <p>Saldo disponível</p>
        <h3 style="color:var(--laranja);font-size:2.5rem;"><?= number_format($user['saldo_carteira'], 2) ?> MT</h3>
    </div>

    <div class="form-container" style="max-width:450px;margin:0 0 40px 0;">
        <h3 style="margin-bottom:15px;">Carregar Saldo</h3>
        <p style="font-size:0.9rem;color:#666;margin-bottom:15px;">
            Simulação de pagamento via M-Pesa ou E-Mola (para demonstração académica)
        </p>
        <form method="POST">
            <div class="form-group">
                <label>Valor (MT)</label>
                <input type="number" name="valor" min="50" step="50" value="200" required>
            </div>
            <div class="form-group">
                <label>Método</label>
                <select name="metodo">
                    <option value="mpesa">M-Pesa</option>
                    <option value="emola">E-Mola</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">
                <i class="fas fa-mobile-alt"></i> Carregar Agora
            </button>
        </form>
    </div>

    <h2 class="secao-titulo"><i class="fas fa-history"></i> Histórico</h2>
    <?php if (empty($historico)): ?>
        <p>Ainda não há movimentos.</p>
    <?php else: ?>
        <table class="tabela">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historico as $h): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($h['criado_em'])) ?></td>
                        <td><?= ucfirst($h['tipo']) ?></td>
                        <td><?= htmlspecialchars($h['descricao']) ?></td>
                        <td style="color:<?= $h['valor'] >= 0 ? 'var(--sucesso)' : 'var(--erro)' ?>">
                            <?= $h['valor'] >= 0 ? '+' : '' ?><?= number_format($h['valor'], 2) ?> MT
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
