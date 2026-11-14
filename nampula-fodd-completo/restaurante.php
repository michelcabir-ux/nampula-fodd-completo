<?php
/**
 * Página pública do restaurante + cardápio
 */
require_once 'includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    redirect('index.php');
}

$stmt = $pdo->prepare("SELECT r.*, 
    COALESCE(AVG(c.nota), 0) as media_estrelas,
    COUNT(DISTINCT c.id) as total_comentarios
    FROM restaurantes r
    LEFT JOIN comentarios c ON c.restaurante_id = r.id
    WHERE r.id = ? AND r.activo = 1
    GROUP BY r.id");
$stmt->execute([$id]);
$rest = $stmt->fetch();

if (!$rest) {
    echo '<div class="container" style="padding:40px;"><div class="alert alert-erro">Restaurante não encontrado.</div></div>';
    require_once 'includes/footer.php';
    exit;
}

$titulo = $rest['nome'];

// Produtos activos
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE restaurante_id = ? AND activo = 1 ORDER BY prato_do_dia DESC, categoria, nome");
$stmt->execute([$id]);
$produtos = $stmt->fetchAll();

// Prato do dia
$pratoDia = null;
foreach ($produtos as $p) {
    if ($p['prato_do_dia']) {
        $pratoDia = $p;
        break;
    }
}

// Comentários
$stmt = $pdo->prepare("SELECT c.*, u.nome as cliente_nome 
    FROM comentarios c 
    JOIN usuarios u ON u.id = c.cliente_id
    WHERE c.restaurante_id = ?
    ORDER BY c.criado_em DESC LIMIT 20");
$stmt->execute([$id]);
$comentarios = $stmt->fetchAll();
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<div class="container" style="padding-top:30px;">
    <div class="rest-header" style="display:flex;gap:25px;flex-wrap:wrap;margin-bottom:30px;background:white;padding:25px;border-radius:var(--radius);box-shadow:var(--sombra);">
        <img src="<?= url('uploads/' . ($rest['foto'] ?: 'default-rest.jpg')) ?>"
             alt="<?= htmlspecialchars($rest['nome']) ?>"
             style="width:200px;height:150px;object-fit:cover;border-radius:10px;"
             onerror="this.src='https://via.placeholder.com/200x150/FF6B00/ffffff?text=Rest'">
        <div style="flex:1;">
            <h1 style="margin-bottom:8px;"><?= htmlspecialchars($rest['nome']) ?></h1>
            <p style="color:var(--texto-claro);margin-bottom:10px;"><?= htmlspecialchars($rest['descricao'] ?? '') ?></p>
            <p>
                <span class="estrelas"><i class="fas fa-star"></i> <?= number_format($rest['media_estrelas'], 1) ?></span>
                (<?= $rest['total_comentarios'] ?> avaliações)
                · <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($rest['cidade']) ?>
                · <?= htmlspecialchars($rest['tipo_comida']) ?>
                · <?= $rest['aberto'] ? '<span style="color:var(--sucesso);">Aberto</span>' : '<span style="color:var(--erro);">Fechado</span>' ?>
            </p>
            <?php if ($rest['endereco']): ?>
                <p style="font-size:0.9rem;margin-top:5px;"><i class="fas fa-home"></i> <?= htmlspecialchars($rest['endereco']) ?></p>
            <?php endif; ?>
            <?php if ($rest['latitude'] && $rest['longitude']): ?>
                <a href="https://www.google.com/maps?q=<?= $rest['latitude'] ?>,<?= $rest['longitude'] ?>"
                   target="_blank" rel="noopener" style="color:var(--laranja);font-size:0.9rem;">
                    <i class="fas fa-map"></i> Ver no Google Maps
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($pratoDia): 
        $precoDia = $pratoDia['preco'] * (1 - $pratoDia['desconto_prato_dia'] / 100);
    ?>
        <div style="background:linear-gradient(135deg,#FF6B00,#E05A00);color:white;padding:20px;border-radius:var(--radius);margin-bottom:30px;display:flex;gap:20px;align-items:center;flex-wrap:wrap;">
            <div>
                <span class="badge-dia" style="background:white;color:var(--laranja);">🔥 PRATO DO DIA</span>
                <h2 style="margin:10px 0 5px;"><?= htmlspecialchars($pratoDia['nome']) ?></h2>
                <p style="opacity:0.9;"><?= htmlspecialchars($pratoDia['descricao'] ?? '') ?></p>
                <p style="margin-top:10px;">
                    <span style="text-decoration:line-through;opacity:0.7;"><?= number_format($pratoDia['preco'], 2) ?> MT</span>
                    <strong style="font-size:1.4rem;margin-left:10px;"><?= number_format($precoDia, 2) ?> MT</strong>
                    <span style="background:white;color:var(--laranja);padding:2px 8px;border-radius:20px;font-size:0.85rem;margin-left:8px;">
                        -<?= number_format($pratoDia['desconto_prato_dia'], 0) ?>%
                    </span>
                </p>
            </div>
            <?php if (estaLogado() && temPermissao('cliente') && $rest['aberto']): ?>
                <button class="btn-add-carrinho" data-id="<?= $pratoDia['id'] ?>"
                        style="background:white;color:var(--laranja);border:none;padding:12px 25px;border-radius:50px;font-weight:600;cursor:pointer;margin-left:auto;">
                    <i class="fas fa-plus"></i> Adicionar
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <h2 class="secao-titulo"><i class="fas fa-utensils"></i> Cardápio</h2>

    <?php if (empty($produtos)): ?>
        <p>Este restaurante ainda não tem produtos no cardápio.</p>
    <?php else: ?>
        <?php
        $categorias = [];
        foreach ($produtos as $p) {
            $categorias[$p['categoria']][] = $p;
        }
        foreach ($categorias as $cat => $items):
        ?>
            <h3 style="margin:25px 0 15px;color:var(--laranja);"><?= htmlspecialchars($cat) ?></h3>
            <div class="produtos-grid">
                <?php foreach ($items as $p): 
                    $precoFinal = $p['prato_do_dia'] ? $p['preco'] * (1 - $p['desconto_prato_dia']/100) : $p['preco'];
                ?>
                    <div class="produto-card">
                        <img src="<?= url('uploads/' . ($p['foto'] ?: 'default-prato.jpg')) ?>"
                             alt="<?= htmlspecialchars($p['nome']) ?>"
                             onerror="this.src='https://via.placeholder.com/300x180/FF6B00/ffffff?text=Prato'">
                        <div class="produto-info">
                            <h4>
                                <?= htmlspecialchars($p['nome']) ?>
                                <?php if ($p['prato_do_dia']): ?>
                                    <span class="badge-dia">DIA</span>
                                <?php endif; ?>
                            </h4>
                            <p style="font-size:0.9rem;color:var(--texto-claro);"><?= htmlspecialchars($p['descricao'] ?? '') ?></p>
                            <div class="produto-preco">
                                <?php if ($p['prato_do_dia'] && $p['desconto_prato_dia'] > 0): ?>
                                    <span style="text-decoration:line-through;color:#999;font-size:0.9rem;"><?= number_format($p['preco'], 2) ?></span>
                                    <strong><?= number_format($precoFinal, 2) ?> MT</strong>
                                <?php else: ?>
                                    <strong><?= number_format($p['preco'], 2) ?> MT</strong>
                                <?php endif; ?>
                            </div>
                            <?php if (estaLogado() && temPermissao('cliente') && $rest['aberto']): ?>
                                <button class="btn-add-carrinho btn-primary" data-id="<?= $p['id'] ?>" style="margin-top:10px;">
                                    <i class="fas fa-plus"></i> Adicionar
                                </button>
                            <?php elseif (!estaLogado()): ?>
                                <a href="<?= url('login.php') ?>" style="font-size:0.85rem;color:var(--laranja);">Faça login para pedir</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2 class="secao-titulo" style="margin-top:40px;"><i class="fas fa-comments"></i> Avaliações</h2>
    <?php if (empty($comentarios)): ?>
        <p>Ainda não há avaliações para este restaurante.</p>
    <?php else: ?>
        <?php foreach ($comentarios as $c): ?>
            <div style="background:white;padding:15px 20px;border-radius:10px;margin-bottom:12px;box-shadow:var(--sombra);">
                <div style="display:flex;justify-content:space-between;flex-wrap:wrap;">
                    <strong><?= htmlspecialchars($c['cliente_nome']) ?></strong>
                    <span class="estrelas">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?= $i <= $c['nota'] ? '' : '-o' ?>"></i>
                        <?php endfor; ?>
                    </span>
                </div>
                <?php if ($c['texto']): ?>
                    <p style="margin-top:8px;"><?= htmlspecialchars($c['texto']) ?></p>
                <?php endif; ?>
                <small style="color:#999;"><?= date('d/m/Y', strtotime($c['criado_em'])) ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
