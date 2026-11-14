<?php
/**
 * Dashboard Admin
 */
$titulo = 'Admin Dashboard';
require_once '../includes/header.php';
proteger(['admin']);

$stats = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM usuarios WHERE tipo = 'cliente') as total_clientes,
    (SELECT COUNT(*) FROM restaurantes WHERE activo = 1) as total_restaurantes,
    (SELECT COUNT(*) FROM pedidos) as total_pedidos,
    (SELECT COALESCE(SUM(minha_comissao_10), 0) FROM pedidos WHERE status NOT IN ('recusado','cancelado')) as total_comissao
")->fetch();
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<div class="container" style="padding-top:30px;">
    <h1 class="secao-titulo"><i class="fas fa-cog"></i> Painel Administrativo</h1>

    <div class="dashboard-grid">
        <div class="stat-card">
            <h3><?= $stats['total_clientes'] ?></h3>
            <p>Clientes</p>
        </div>
        <div class="stat-card">
            <h3><?= $stats['total_restaurantes'] ?></h3>
            <p>Restaurantes</p>
        </div>
        <div class="stat-card">
            <h3><?= $stats['total_pedidos'] ?></h3>
            <p>Pedidos</p>
        </div>
        <div class="stat-card">
            <h3 style="color:var(--sucesso)"><?= number_format($stats['total_comissao'], 2) ?> MT</h3>
            <p>Total Comissão (10%)</p>
        </div>
    </div>

    <div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:30px;">
        <a href="carteira.php" class="btn-ver" style="width:auto;">💰 Minha Carteira</a>
        <a href="pedidos.php" class="btn-ver" style="width:auto;">📋 Todos os Pedidos</a>
        <a href="restaurantes.php" class="btn-ver" style="width:auto;">🏪 Gerir Restaurantes</a>
    </div>

    <h2 class="secao-titulo">Pedidos Recentes</h2>
    <?php
    $pedidos = $pdo->query("SELECT p.*, r.nome as rest_nome, u.nome as cliente_nome
        FROM pedidos p
        JOIN restaurantes r ON r.id = p.restaurante_id
        JOIN usuarios u ON u.id = p.cliente_id
        ORDER BY p.criado_em DESC LIMIT 15")->fetchAll();
    ?>
    <table class="tabela">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Restaurante</th>
                <th>Total</th>
                <th>Comissão 10%</th>
                <th>Rest. 90%</th>
                <th>Status</th>
                <th>Saque</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td>#<?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['cliente_nome']) ?></td>
                    <td><?= htmlspecialchars($p['rest_nome']) ?></td>
                    <td><?= number_format($p['valor_total'], 2) ?></td>
                    <td style="color:var(--sucesso);font-weight:600;"><?= number_format($p['minha_comissao_10'], 2) ?></td>
                    <td><?= number_format($p['valor_restaurante_90'], 2) ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', $p['status'])) ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', $p['status_saque'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
