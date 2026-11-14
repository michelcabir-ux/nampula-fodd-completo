<?php
/**
 * Todos os pedidos (Admin)
 */
$titulo = 'Todos os Pedidos';
require_once '../includes/header.php';
proteger(['admin']);

$pedidos = $pdo->query("SELECT p.*, r.nome as rest_nome, u.nome as cliente_nome
    FROM pedidos p
    JOIN restaurantes r ON r.id = p.restaurante_id
    JOIN usuarios u ON u.id = p.cliente_id
    ORDER BY p.criado_em DESC")->fetchAll();
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<div class="container" style="padding-top:30px;">
    <h1 class="secao-titulo"><i class="fas fa-list"></i> Todos os Pedidos</h1>
    <p><a href="dashboard.php" style="color:var(--laranja)">← Voltar</a></p>

    <table class="tabela">
        <thead>
            <tr>
                <th>#</th>
                <th>Data</th>
                <th>Cliente</th>
                <th>Restaurante</th>
                <th>Total</th>
                <th>Comissão 10%</th>
                <th>Rest 90%</th>
                <th>Status</th>
                <th>Pagamento</th>
                <th>Saque</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td>#<?= $p['id'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?></td>
                    <td><?= htmlspecialchars($p['cliente_nome']) ?></td>
                    <td><?= htmlspecialchars($p['rest_nome']) ?></td>
                    <td><?= number_format($p['valor_total'], 2) ?></td>
                    <td style="color:var(--sucesso)"><?= number_format($p['minha_comissao_10'], 2) ?></td>
                    <td><?= number_format($p['valor_restaurante_90'], 2) ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', $p['status'])) ?></td>
                    <td><?= $p['forma_pagamento'] ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', $p['status_saque'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
