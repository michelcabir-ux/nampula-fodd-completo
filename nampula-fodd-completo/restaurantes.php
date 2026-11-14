<?php
/**
 * Página pública de listagem de restaurantes
 * (com filtros e GPS)
 */
$titulo = 'Restaurantes';
require_once 'includes/header.php';

$cidade = $_GET['cidade'] ?? '';
$tipo   = $_GET['tipo'] ?? '';
$lat    = $_GET['lat'] ?? null;
$lng    = $_GET['lng'] ?? null;
$q      = trim($_GET['q'] ?? '');

$sql = "SELECT r.*, 
               COALESCE(AVG(c.nota), 0) as media_estrelas,
               COUNT(DISTINCT c.id) as total_comentarios
        FROM restaurantes r
        LEFT JOIN comentarios c ON c.restaurante_id = r.id
        WHERE r.activo = 1";
$params = [];

if ($cidade) {
    $sql .= " AND r.cidade = ?";
    $params[] = $cidade;
}
if ($tipo) {
    $sql .= " AND r.tipo_comida LIKE ?";
    $params[] = "%$tipo%";
}
if ($q) {
    $sql .= " AND (r.nome LIKE ? OR r.descricao LIKE ? OR r.tipo_comida LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$sql .= " GROUP BY r.id ORDER BY r.aberto DESC, media_estrelas DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$restaurantes = $stmt->fetchAll();

if ($lat && $lng) {
    foreach ($restaurantes as &$r) {
        if ($r['latitude'] && $r['longitude']) {
            $dlat = deg2rad($r['latitude'] - $lat);
            $dlng = deg2rad($r['longitude'] - $lng);
            $a = sin($dlat/2)**2 + cos(deg2rad($lat)) * cos(deg2rad($r['latitude'])) * sin($dlng/2)**2;
            $r['distancia'] = 6371 * 2 * asin(sqrt($a));
        } else {
            $r['distancia'] = 9999;
        }
    }
    usort($restaurantes, fn($a, $b) => $a['distancia'] <=> $b['distancia']);
}
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<section class="hero" style="padding:40px 0;">
    <div class="container">
        <h1><i class="fas fa-store"></i> Todos os Restaurantes</h1>
        <p>Encontre o melhor sítio para comer em Nampula e Nacala</p>
    </div>
</section>

<div class="container">
    <form class="filtros" method="GET" action="<?= url('restaurantes.php') ?>">
        <input type="text" name="q" placeholder="Pesquisar restaurante..." value="<?= htmlspecialchars($q) ?>"
               style="padding:10px 15px;border:1px solid #ddd;border-radius:8px;min-width:200px;">
        <select name="cidade">
            <option value="">Todas as cidades</option>
            <option value="Nampula" <?= $cidade === 'Nampula' ? 'selected' : '' ?>>Nampula</option>
            <option value="Nacala-Porto" <?= $cidade === 'Nacala-Porto' ? 'selected' : '' ?>>Nacala-Porto</option>
            <option value="Nacala-a-Velha" <?= $cidade === 'Nacala-a-Velha' ? 'selected' : '' ?>>Nacala-a-Velha</option>
        </select>
        <select name="tipo">
            <option value="">Todos os tipos</option>
            <option value="Moçambicana" <?= $tipo === 'Moçambicana' ? 'selected' : '' ?>>Moçambicana</option>
            <option value="Pizza" <?= $tipo === 'Pizza' ? 'selected' : '' ?>>Pizza</option>
            <option value="Fast Food" <?= $tipo === 'Fast Food' ? 'selected' : '' ?>>Fast Food</option>
            <option value="Marisco" <?= $tipo === 'Marisco' ? 'selected' : '' ?>>Marisco</option>
        </select>
        <button type="submit" class="btn-primary" style="width:auto;padding:10px 20px;">
            <i class="fas fa-search"></i> Filtrar
        </button>
        <button type="button" id="btnGps" class="btn-primary" style="width:auto;padding:10px 20px;background:#17a2b8;">
            <i class="fas fa-location-arrow"></i> Perto de mim
        </button>
    </form>

    <p style="margin-bottom:20px;color:var(--texto-claro);">
        <?= count($restaurantes) ?> restaurante(s) encontrado(s)
        <?php if ($lat && $lng): ?> · ordenados por distância<?php endif; ?>
    </p>

    <?php if (empty($restaurantes)): ?>
        <div style="text-align:center;padding:60px 20px;">
            <i class="fas fa-search" style="font-size:3rem;color:#ccc;margin-bottom:15px;"></i>
            <p>Nenhum restaurante encontrado.</p>
            <a href="<?= url('restaurantes.php') ?>" class="btn-primary" style="width:auto;display:inline-block;margin-top:15px;">Limpar filtros</a>
        </div>
    <?php else: ?>
        <div class="restaurantes-grid">
            <?php foreach ($restaurantes as $r): ?>
                <div class="restaurante-card">
                    <div class="rest-img">
                        <img src="<?= url('uploads/' . ($r['foto'] ?: 'default-rest.jpg')) ?>"
                             alt="<?= htmlspecialchars($r['nome']) ?>"
                             onerror="this.src='https://via.placeholder.com/400x200/FF6B00/ffffff?text=Restaurante'">
                        <?php if (!$r['aberto']): ?>
                            <span class="badge-fechado">Fechado</span>
                        <?php endif; ?>
                    </div>
                    <div class="rest-info">
                        <h3><?= htmlspecialchars($r['nome']) ?></h3>
                        <p style="font-size:0.9rem;color:var(--texto-claro);margin:5px 0;">
                            <?= htmlspecialchars(mb_strimwidth($r['descricao'] ?? '', 0, 80, '...')) ?>
                        </p>
                        <p class="rest-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($r['cidade']) ?></span>
                            <span><i class="fas fa-utensils"></i> <?= htmlspecialchars($r['tipo_comida']) ?></span>
                        </p>
                        <div class="rest-rating">
                            <span class="estrelas">
                                <i class="fas fa-star"></i> <?= number_format($r['media_estrelas'], 1) ?>
                            </span>
                            <span>(<?= $r['total_comentarios'] ?>)</span>
                            <?php if (isset($r['distancia']) && $r['distancia'] < 9999): ?>
                                <span style="margin-left:auto;color:var(--laranja);">
                                    <?= number_format($r['distancia'], 1) ?> km
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($r['latitude'] && $r['longitude']): ?>
                            <a href="https://www.google.com/maps?q=<?= $r['latitude'] ?>,<?= $r['longitude'] ?>"
                               target="_blank" rel="noopener"
                               style="font-size:0.85rem;color:var(--laranja);display:inline-block;margin:8px 0;">
                                <i class="fas fa-map"></i> Ver no Mapa
                            </a>
                        <?php endif; ?>
                        <a href="<?= url('restaurante.php?id=' . $r['id']) ?>" class="btn-ver">Ver Cardápio</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
