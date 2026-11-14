<?php
/**
 * Dashboard do Restaurante
 */
$titulo = 'Dashboard Restaurante';
require_once '../includes/header.php';
proteger(['restaurante']);

$user = utilizadorAtual();
$stmt = $pdo->prepare("SELECT * FROM restaurantes WHERE usuario_id = ?");
$stmt->execute([$user['id']]);
$rest = $stmt->fetch();

if (!$rest) {
    echo '<div class="container" style="padding:40px;"><div class="alert alert-erro">Ainda não registou o seu restaurante. <a href="criar_restaurante.php">Criar agora</a></div></div>';
    require_once '../includes/footer.php';
    exit;
}

// Acções de pedidos
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $pedidoId = (int)$_GET['id'];
    $acao = $_GET['acao'];
    $statusMap = [
        'aceitar'  => 'aceito',
        'recusar'  => 'recusado',
        'preparar' => 'em_preparo',
        'entregar' => 'entregue'
    ];
    if (isset($statusMap[$acao])) {
        $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ? AND restaurante_id = ?");
        $stmt->execute([$statusMap[$acao], $pedidoId, $rest['id']]);
    }
    redirect('restaurante/dashboard.php');
}

// Solicitar saque
if (isset($_GET['solicitar_saque'])) {
    $stmt = $pdo->prepare("UPDATE pedidos SET status_saque = 'solicitado' 
        WHERE restaurante_id = ? AND status = 'entregue' AND status_saque = 'nao_solicitado'");
    $stmt->execute([$rest['id']]);
    redirect('restaurante/dashboard.php');
}

// Alternar aberto/fechado
if (isset($_GET['toggle_aberto'])) {
    $novo = $rest['aberto'] ? 0 : 1;
    $pdo->prepare("UPDATE restaurantes SET aberto = ? WHERE id = ?")->execute([$novo, $rest['id']]);
    redirect('restaurante/dashboard.php');
}

$stmt = $pdo->prepare("SELECT p.*, u.nome as cliente_nome, u.telefone as cliente_tel
    FROM pedidos p 
    JOIN usuarios u ON u.id = p.cliente_id
    WHERE p.restaurante_id = ?
    ORDER BY FIELD(p.status, 'pendente', 'aceito', 'em_preparo', 'entregue', 'recusado', 'cancelado'), p.criado_em DESC
    LIMIT 40");
$stmt->execute([$rest['id']]);
$pedidos = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_pedidos,
    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
    COALESCE(SUM(CASE WHEN status = 'entregue' THEN valor_restaurante_90 ELSE 0 END), 0) as total_ganho,
    COALESCE(SUM(CASE WHEN status = 'entregue' AND status_saque = 'nao_solicitado' THEN valor_restaurante_90 ELSE 0 END), 0) as disponivel_saque
    FROM pedidos WHERE restaurante_id = ? AND status != 'recusado'");
$stmt->execute([$rest['id']]);
$stats = $stmt->fetch();
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<div class="container" style="padding-top:30px;">
    <h1 class="secao-titulo"><i class="fas fa-tachometer-alt"></i> Dashboard - <?= htmlspecialchars($rest['nome']) ?></h1>

    <div style="margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap;">
        <a href="cardapio.php" class="btn-ver" style="width:auto;">📋 Gerir Cardápio</a>
        <a href="?toggle_aberto=1" class="btn-ver" style="width:auto;background:<?= $rest['aberto'] ? 'var(--sucesso)' : 'var(--erro)' ?>;">
            <?= $rest['aberto'] ? '🟢 Aberto (clique para fechar)' : '🔴 Fechado (clique para abrir)' ?>
        </a>
    </div>

    <div class="dashboard-grid">
        <div class="stat-card">
            <h3><?= (int)$stats['total_pedidos'] ?></h3>
            <p>Total Pedidos</p>
        </div>
        <div class="stat-card">
            <h3 style="color:var(--laranja)"><?= (int)$stats['pendentes'] ?></h3>
            <p>Pendentes</p>
        </div>
        <div class="stat-card">
            <h3 style="color:var(--sucesso)"><?= number_format($stats['total_ganho'], 2) ?> MT</h3>
            <p>Total Ganho (90%)</p>
        </div>
        <div class="stat-card">
            <h3><?= number_format($stats['disponivel_saque'], 2) ?> MT</h3>
            <p>Disponível para saque</p>
            <?php if ($stats['disponivel_saque'] > 0): ?>
                <a href="?solicitar_saque=1" class="btn-primary" style="margin-top:10px;width:auto;padding:6px 14px;font-size:0.85rem;"
                   data-confirm="Solicitar saque de <?= number_format($stats['disponivel_saque'], 2) ?> MT?">
                    Solicitar Saque
                </a>
            <?php endif; ?>
        </div>
    </div>

    <h2 class="secao-titulo">Pedidos</h2>
    <?php if (empty($pedidos)): ?>
        <p>Ainda não há pedidos.</p>
    <?php else: ?>
        <table class="tabela">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Seu (90%)</th>
                    <th>Status</th>
                    <th>Pagamento</th>
                    <th>Acções</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td>#<?= $p['id'] ?><br><small><?= date('d/m H:i', strtotime($p['criado_em'])) ?></small></td>
                        <td>
                            <?= htmlspecialchars($p['cliente_nome']) ?><br>
                            <small><?= htmlspecialchars($p['cliente_tel'] ?? '') ?></small>
                        </td>
                        <td><?= number_format($p['valor_total'], 2) ?> MT</td>
                        <td><?= number_format($p['valor_restaurante_90'], 2) ?> MT</td>
                        <td><?= ucfirst(str_replace('_', ' ', $p['status'])) ?></td>
                        <td><?= $p['forma_pagamento'] === 'carteira' ? 'Carteira' : 'Entrega' ?></td>
                        <td style="white-space:nowrap;">
                            <?php if ($p['status'] === 'pendente'): ?>
                                <a href="?acao=aceitar&id=<?= $p['id'] ?>" style="color:var(--sucesso);margin-right:8px;" title="Aceitar"><i class="fas fa-check"></i></a>
                                <a href="?acao=recusar&id=<?= $p['id'] ?>" style="color:var(--erro);" title="Recusar" data-confirm="Recusar este pedido?"><i class="fas fa-times"></i></a>
                            <?php elseif ($p['status'] === 'aceito'): ?>
                                <a href="?acao=preparar&id=<?= $p['id'] ?>" style="color:var(--laranja);" title="Em preparo"><i class="fas fa-fire"></i> Preparar</a>
                            <?php elseif ($p['status'] === 'em_preparo'): ?>
                                <a href="?acao=entregar&id=<?= $p['id'] ?>" style="color:var(--sucesso);" title="Marcar entregue"><i class="fas fa-motorcycle"></i> Entregar</a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
