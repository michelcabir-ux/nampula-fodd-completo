<?php
/**
 * Criar / Registar Restaurante (com upload de foto)
 */
$titulo = 'Criar Restaurante';
require_once '../includes/header.php';
proteger(['restaurante']);

$user = utilizadorAtual();

// Já tem restaurante?
$stmt = $pdo->prepare("SELECT id FROM restaurantes WHERE usuario_id = ?");
$stmt->execute([$user['id']]);
if ($stmt->fetch()) {
    redirect('restaurante/dashboard.php');
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $cidade   = $_POST['cidade'] ?? '';
    $tipo     = trim($_POST['tipo_comida'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $lat      = floatval($_POST['latitude'] ?? 0) ?: null;
    $lng      = floatval($_POST['longitude'] ?? 0) ?: null;

    $cidadesOk = ['Nampula', 'Nacala-Porto', 'Nacala-a-Velha'];

    if (strlen($nome) < 3) {
        $erro = 'Nome do restaurante demasiado curto.';
    } elseif (!in_array($cidade, $cidadesOk)) {
        $erro = 'Seleccione uma cidade válida.';
    } else {
        $foto = 'default-rest.jpg';
        if (!empty($_FILES['foto']['name'])) {
            $up = uploadImagem($_FILES['foto'], 'uploads/', 'rest_');
            if ($up) {
                $foto = $up;
            } else {
                $erro = 'Erro no upload da imagem (use JPG/PNG até 5MB).';
            }
        }

        if (!$erro) {
            $stmt = $pdo->prepare("INSERT INTO restaurantes (usuario_id, nome, descricao, cidade, tipo_comida, endereco, latitude, longitude, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user['id'], $nome, $descricao, $cidade, $tipo, $endereco, $lat, $lng, $foto]);
            redirect('restaurante/dashboard.php');
        }
    }
}
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<div class="container" style="padding-top:30px;">
    <h1 class="secao-titulo"><i class="fas fa-store"></i> Registar o seu Restaurante</h1>

    <?php if ($erro): ?><div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

    <div class="form-container" style="max-width:600px;">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nome do restaurante *</label>
                <input type="text" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" rows="3"><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Cidade *</label>
                <select name="cidade" required>
                    <option value="">Seleccione...</option>
                    <option value="Nampula">Nampula</option>
                    <option value="Nacala-Porto">Nacala-Porto</option>
                    <option value="Nacala-a-Velha">Nacala-a-Velha</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tipo de comida</label>
                <input type="text" name="tipo_comida" placeholder="Ex: Moçambicana, Pizza, Fast Food" value="<?= htmlspecialchars($_POST['tipo_comida'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Endereço</label>
                <input type="text" name="endereco" value="<?= htmlspecialchars($_POST['endereco'] ?? '') ?>">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div class="form-group">
                    <label>Latitude (GPS)</label>
                    <input type="text" name="latitude" placeholder="-15.1167" value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Longitude (GPS)</label>
                    <input type="text" name="longitude" placeholder="39.2667" value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Foto do restaurante</label>
                <input type="file" name="foto" accept="image/jpeg,image/png,image/webp,image/gif">
                <small style="color:#666;">JPG, PNG ou WebP · máximo 5 MB</small>
            </div>
            <button type="submit" class="btn-primary">
                <i class="fas fa-check"></i> Criar Restaurante
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
