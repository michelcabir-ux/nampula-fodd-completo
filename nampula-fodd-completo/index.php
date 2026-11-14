<?php
/**
 * Nampula Fodd - Página Inicial
 */
$titulo = 'Início';
require_once 'includes/header.php';

$cidade = $_GET['cidade'] ?? '';
$tipo   = $_GET['tipo'] ?? '';
$lat    = $_GET['lat'] ?? null;
$lng    = $_GET['lng'] ?? null;

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

<section class="hero">
    <div class="container">
        <h1>Comida deliciosa em Nampula e Nacala</h1>
        <p>Os melhores restaurantes, takeaways e lanchonetes num só lugar</p>
    </div>
</section>

<div class="container">
    <form class="filtros" method="GET" action="<?= url('index.php') ?>">
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
            <option value="Cafés" <?= $tipo === 'Cafés' ? 'selected' : '' ?>>Cafés</option>
        </select>

        <button type="submit" class="btn-primary" style="width:auto;padding:10px 20px;">
            <i class="fas fa-search"></i> Filtrar
        </button>
        <button type="button" id="btnGps" class="btn-primary" style="width:auto;padding:10px 20px;background:#17a2b8;">
            <i class="fas fa-location-arrow"></i> Perto de mim
        </button>
    </form>

    <h2 class="secao-titulo">
        <i class="fas fa-store"></i> Restaurantes
        <?php if ($lat && $lng): ?>
            <small style="font-weight:400;color:var(--texto-claro);"> (ordenados por distância)</small>
        <?php endif; ?>
    </h2>

    <?php if (empty($restaurantes)): ?>
        <p style="text-align:center;padding:40px;">Nenhum restaurante encontrado com estes filtros.</p>
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
                        <p class="rest-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($r['cidade']) ?></span>
                            <span><i class="fas fa-utensils"></i> <?= htmlspecialchars($r['tipo_comida']) ?></span>
                        </p>
                        <div class="rest-rating">
                            <span class="estrelas">
                                <i class="fas fa-star"></i>
                                <?= number_format($r['media_estrelas'], 1) ?>
                            </span>
                            <span>(<?= $r['total_comentarios'] ?> avaliações)</span>
                            <?php if (isset($r['distancia']) && $r['distancia'] < 9999): ?>
                                <span style="margin-left:auto;color:var(--laranja);">
                                    <i class="fas fa-location-arrow"></i> <?= number_format($r['distancia'], 1) ?> km
                                </span>
                            <?php endif; ?>
                        </div>
                        <a href="<?= url('restaurante.php?id=' . $r['id']) ?>" class="btn-ver">
                            Ver Cardápio
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
