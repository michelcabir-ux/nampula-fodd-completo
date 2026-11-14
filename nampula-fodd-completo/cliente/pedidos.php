<?php
/**
 * Meus Pedidos + Avaliações
 */
$titulo = 'Meus Pedidos';
require_once '../includes/header.php';
proteger(['cliente']);

$user = utilizadorAtual();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentar'])) {
    $pedidoId = (int)$_POST['pedido_id'];
    $nota = (int)$_POST['nota'];
    $texto = trim($_POST['texto'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? AND cliente_id = ? AND status = 'entregue'");
    $stmt->execute([$pedidoId, $user['id']]);
    $pedido = $stmt->fetch();

    if ($pedido && $nota >= 1 && $nota <= 5) {
        $stmt = $pdo->prepare("SELECT id FROM comentarios WHERE pedido_id = ?");
        $stmt->execute([$pedidoId]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO comentarios (cliente_id, restaurante_id, pedido_id, nota, texto) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user['id'], $pedido['restaurante_id'], $pedidoId, $nota, $texto]);
            $msg = '<div class="alert alert-sucesso">Avaliação enviada! Obrigado.</div>';
        } else {
            $msg = '<div class="alert alert-erro">Já avaliou este pedido.</div>';
        }
    }
}

$stmt = $pdo->prepare("SELECT p.*, r.nome as restaurante_nome 
    FROM pedidos p 
    JOIN restaurantes r ON r.id = p.restaurante_id 
    WHERE p.cliente_id = ? 
    ORDER BY p.criado_em DESC");
$stmt->execute([$user['id']]);
$pedidos = $stmt->fetchAll();
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<div class="container" style="padding-top:30px;">
    <h1 class="secao-titulo"><i class="fas fa-receipt"></i> Meus Pedidos</h1>
    <?= $msg ?>

    <?php if (empty($pedidos)): ?>
        <p>Ainda não fez nenhum pedido. <a href="<?= url('index.php') ?>" style="color:var(--laranja)">Começar a pedir</a></p>
    <?php else: ?>
        <?php foreach ($pedidos as $p): ?>
            <div style="background:white;border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:var(--sombra);">
                <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div>
                        <strong>#<?= $p['id'] ?> - <?= htmlspecialchars($p['restaurante_nome']) ?></strong><br>
                        <small><?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?></small>
                    </div>
                    <div>
                        <span style="padding:4px 12px;border-radius:20px;font-size:0.85rem;font-weight:600;background:<?= 
                            in_array($p['status'], ['entregue']) ? '#d4edda' : 
                            (in_array($p['status'], ['recusado','cancelado']) ? '#f8d7da' : '#fff3cd') 
                        ?>;color:<?= 
                            $p['status'] === 'entregue' ? 'var(--sucesso)' : 
                            (in_array($p['status'], ['recusado','cancelado']) ? 'var(--erro)' : 'var(--laranja)') 
                        ?>">
                            <?= ucfirst(str_replace('_', ' ', $p['status'])) ?>
                        </span>
                    </div>
                </div>
                <p style="margin-top:10px;">
                    Total: <strong><?= number_format($p['valor_total'], 2) ?> MT</strong>
                    (<?= $p['forma_pagamento'] === 'carteira' ? 'Carteira' : 'Na entrega' ?>)
                </p>

                <?php if ($p['status'] === 'entregue'):
                    $stmtC = $pdo->prepare("SELECT id FROM comentarios WHERE pedido_id = ?");
                    $stmtC->execute([$p['id']]);
                    $jaComentou = $stmtC->fetch();
                ?>
                    <?php if (!$jaComentou): ?>
                        <form method="POST" style="margin-top:15px;border-top:1px solid #eee;padding-top:15px;">
                            <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                            <div class="form-group">
                                <label>Avaliar (1 a 5 estrelas)</label>
                                <select name="nota" required>
                                    <option value="5">5 - Excelente</option>
                                    <option value="4">4 - Muito Bom</option>
                                    <option value="3">3 - Bom</option>
                                    <option value="2">2 - Razoável</option>
                                    <option value="1">1 - Mau</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Comentário</label>
                                <textarea name="texto" rows="2" placeholder="Como foi a experiência?"></textarea>
                            </div>
                            <button type="submit" name="comentar" class="btn-primary" style="width:auto;">Enviar Avaliação</button>
                        </form>
                    <?php else: ?>
                        <p style="color:var(--sucesso);margin-top:10px;"><i class="fas fa-check"></i> Já avaliou este pedido</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
