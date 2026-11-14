<?php
/**
 * Gestão de Restaurantes (Admin) - NOVO
 */
$titulo = 'Gerir Restaurantes';
require_once '../includes/header.php';
proteger(['admin']);

$msg = '';

// Activar / Desactivar
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE restaurantes SET activo = IF(activo=1, 0, 1) WHERE id = ?");
    $stmt->execute([$id]);
    $msg = '<div class="alert alert-sucesso">Estado do restaurante actualizado.</div>';
}

// Abrir / Fechar
if (isset($_GET['toggle_aberto']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE restaurantes SET aberto = IF(aberto=1, 0, 1) WHERE id = ?");
    $stmt->execute([$id]);
    $msg = '<div class="alert alert-sucesso">Estado aberto/fechado actualizado.</div>';
}

$restaurantes = $pdo->query("SELECT r.*, u.nome as dono, u.email as dono_email, u.telefone as dono_tel,
    (SELECT COUNT(*) FROM produtos p WHERE p.restaurante_id = r.id) as total_produtos,
    (SELECT COUNT(*) FROM pedidos pe WHERE pe.restaurante_id = r.id) as total_pedidos,
    COALESCE((SELECT AVG(c.nota) FROM comentarios c WHERE c.restaurante_id = r.id), 0) as media
    FROM restaurantes r
    JOIN usuarios u ON u.id = r.usuario_id
    ORDER BY r.criado_em DESC")->fetchAll();
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<div class="container" style="padding-top:30px;">
    <h1 class="secao-titulo"><i class="fas fa-store"></i> Gerir Restaurantes</h1>
    <p><a href="dashboard.php" style="color:var(--laranja)">← Voltar</a></p>
    <?= $msg ?>

    <p style="margin-bottom:20px;"><?= count($restaurantes) ?> restaurante(s) no sistema</p>

    <table class="tabela">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Dono</th>
                <th>Cidade</th>
                <th>Produtos</th>
                <th>Pedidos</th>
                <th>Nota</th>
                <th>Estado</th>
                <th>Acções</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($restaurantes as $r): ?>
                <tr style="<?= !$r['activo'] ? 'opacity:0.6;' : '' ?>">
                    <td>#<?= $r['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($r['nome']) ?></strong><br>
                        <small><?= htmlspecialchars($r['tipo_comida'] ?? '') ?></small>
                    </td>
                    <td>
                        <?= htmlspecialchars($r['dono']) ?><br>
                        <small><?= htmlspecialchars($r['dono_email']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($r['cidade']) ?></td>
                    <td><?= $r['total_produtos'] ?></td>
                    <td><?= $r['total_pedidos'] ?></td>
                    <td>
                        <span class="estrelas"><i class="fas fa-star"></i> <?= number_format($r['media'], 1) ?></span>
                    </td>
                    <td>
                        <?= $r['activo'] ? '<span style="color:var(--sucesso)">Activo</span>' : '<span style="color:var(--erro)">Inactivo</span>' ?><br>
                        <?= $r['aberto'] ? 'Aberto' : 'Fechado' ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="?toggle=1&id=<?= $r['id'] ?>" style="color:<?= $r['activo'] ? 'var(--erro)' : 'var(--sucesso)' ?>;margin-right:10px;"
                           data-confirm="<?= $r['activo'] ? 'Desactivar' : 'Activar' ?> este restaurante?">
                            <i class="fas fa-<?= $r['activo'] ? 'ban' : 'check' ?>"></i>
                            <?= $r['activo'] ? 'Desactivar' : 'Activar' ?>
                        </a>
                        <br>
                        <a href="?toggle_aberto=1&id=<?= $r['id'] ?>" style="color:var(--laranja);font-size:0.85rem;">
                            <?= $r['aberto'] ? 'Fechar' : 'Abrir' ?>
                        </a>
                        <br>
                        <a href="<?= url('restaurante.php?id=' . $r['id']) ?>" target="_blank" style="font-size:0.85rem;color:#666;">
                            Ver público
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
