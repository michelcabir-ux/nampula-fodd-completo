<?php
/**
 * Carteira do Admin - Comissões 10%
 */
$titulo = 'Minha Carteira (Admin)';
require_once '../includes/header.php';
proteger(['admin']);

$msg = '';

// Marcar saque como pago
if (isset($_GET['pagar_saque']) && isset($_GET['pedido_id'])) {
    $pid = (int)$_GET['pedido_id'];
    $stmt = $pdo->prepare("UPDATE pedidos SET status_saque = 'pago' WHERE id = ? AND status_saque = 'solicitado'");
    $stmt->execute([$pid]);
    $msg = '<div class="alert alert-sucesso">Saque marcado como pago.</div>';
}

// Totais
$totais = $pdo->query("SELECT 
    COALESCE(SUM(minha_comissao_10), 0) as total_comissao,
    COALESCE(SUM(CASE WHEN status_saque = 'solicitado' THEN valor_restaurante_90 ELSE 0 END), 0) as saques_pendentes,
    COALESCE(SUM(CASE WHEN status_saque = 'pago' THEN valor_restaurante_90 ELSE 0 END), 0) as saques_pagos
    FROM pedidos WHERE status NOT IN ('recusado','cancelado')")->fetch();

// Histórico de comissões
$comissoes = $pdo->query("SELECT p.id, p.minha_comissao_10, p.valor_total, p.criado_em, r.nome as rest_nome
    FROM pedidos p
    JOIN restaurantes r ON r.id = p.restaurante_id
    WHERE p.status NOT IN ('recusado','cancelado')
    ORDER BY p.criado_em DESC LIMIT 50")->fetchAll();

// Saques solicitados
$saques = $pdo->query("SELECT p.id, p.valor_restaurante_90, p.criado_em, p.status_saque, r.nome as rest_nome, u.nome as dono, u.telefone
    FROM pedidos p
    JOIN restaurantes r ON r.id = p.restaurante_id
    JOIN usuarios u ON u.id = r.usuario_id
    WHERE p.status = 'entregue' AND p.status_saque IN ('solicitado','pago')
    ORDER BY FIELD(p.status_saque, 'solicitado', 'pago'), p.criado_em DESC")->fetchAll();
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<div class="container" style="padding-top:30px;">
    <h1 class="secao-titulo"><i class="fas fa-wallet"></i> Minha Carteira (Comissões 10%)</h1>
    <p><a href="dashboard.php" style="color:var(--laranja);">← Voltar</a></p>
    <?= $msg ?>

    <div class="dashboard-grid">
        <div class="stat-card">
            <h3 style="color:var(--sucesso)"><?= number_format($totais['total_comissao'], 2) ?> MT</h3>
            <p>Total Comissão Recebida</p>
        </div>
        <div class="stat-card">
            <h3 style="color:var(--laranja)"><?= number_format($totais['saques_pendentes'], 2) ?> MT</h3>
            <p>Saques Pendentes</p>
        </div>
        <div class="stat-card">
            <h3><?= number_format($totais['saques_pagos'], 2) ?> MT</h3>
            <p>Saques Já Pagos</p>
        </div>
    </div>

    <h2 class="secao-titulo">Saques Solicitados pelos Restaurantes</h2>
    <?php if (empty($saques)): ?>
        <p>Nenhum saque solicitado.</p>
    <?php else: ?>
        <table class="tabela">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Restaurante</th>
                    <th>Dono</th>
                    <th>Valor (90%)</th>
                    <th>Status</th>
                    <th>Acção</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($saques as $s): ?>
                    <tr>
                        <td>#<?= $s['id'] ?></td>
                        <td><?= htmlspecialchars($s['rest_nome']) ?></td>
                        <td><?= htmlspecialchars($s['dono']) ?><br><small><?= htmlspecialchars($s['telefone'] ?? '') ?></small></td>
                        <td><?= number_format($s['valor_restaurante_90'], 2) ?> MT</td>
                        <td><?= ucfirst($s['status_saque']) ?></td>
                        <td>
                            <?php if ($s['status_saque'] === 'solicitado'): ?>
                                <a href="?pagar_saque=1&pedido_id=<?= $s['id'] ?>" class="btn-primary" style="width:auto;padding:5px 12px;font-size:0.85rem;"
                                   data-confirm="Confirmar pagamento deste saque?">
                                    Marcar como Pago
                                </a>
                            <?php else: ?>
                                <span style="color:var(--sucesso);"><i class="fas fa-check"></i> Pago</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2 class="secao-titulo" style="margin-top:40px;">Histórico de Comissões</h2>
    <table class="tabela">
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Restaurante</th>
                <th>Valor Pedido</th>
                <th>Comissão 10%</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comissoes as $c): ?>
                <tr>
                    <td>#<?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['rest_nome']) ?></td>
                    <td><?= number_format($c['valor_total'], 2) ?> MT</td>
                    <td style="color:var(--sucesso);font-weight:600;"><?= number_format($c['minha_comissao_10'], 2) ?> MT</td>
                    <td><?= date('d/m/Y H:i', strtotime($c['criado_em'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
