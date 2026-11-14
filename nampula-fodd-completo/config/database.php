<?php
/**
 * Nampula Fodd - Configuração da Base de Dados e URLs
 * Altere os dados da BD conforme o seu servidor (XAMPP / Hosting)
 */

// ========== BASE DE DADOS ==========
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // XAMPP: root | Hosting: o utilizador do cPanel
define('DB_PASS', '');              // XAMPP: vazio | Hosting: a sua senha
define('DB_NAME', 'nampula_fodd');

// ========== URL BASE (automático e robusto) ==========
// Calcula a partir da localização deste ficheiro (config/) em relação ao document root
$projectRootFs = realpath(__DIR__ . '/..');          // pasta do projecto no disco
$docRoot       = realpath($_SERVER['DOCUMENT_ROOT']);

if ($projectRootFs && $docRoot && str_starts_with($projectRootFs, $docRoot)) {
    $relative = substr($projectRootFs, strlen($docRoot));
    $basePath = str_replace('\\', '/', $relative);
} else {
    // Fallback: usa SCRIPT_NAME e sobe pastas conhecidas
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $basePath  = preg_replace('#/(admin|cliente|restaurante|includes|config)$#', '', $scriptDir);
}

$basePath = rtrim($basePath, '/');
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
define('BASE_URL', $basePath);

// Função auxiliar para gerar URLs
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

// Conexão PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erro de conexão com a base de dados: " . $e->getMessage() . 
        "<br><br>Verifique se a base de dados <strong>nampula_fodd</strong> existe e se os dados em config/database.php estão correctos.");
}

// Redirecionar
function redirect($path) {
    if (strpos($path, 'http') === 0) {
        header("Location: $path");
        exit;
    }
    $path = ltrim($path, '/');
    header("Location: " . BASE_URL . '/' . $path);
    exit;
}

// Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Upload de imagem seguro
 * @return string|false  Nome do ficheiro ou false em erro
 */
function uploadImagem($file, $pasta = 'uploads/', $prefixo = 'img_') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $tiposPermitidos)) {
        return false;
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5 MB
        return false;
    }

    $ext = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => 'jpg'
    };

    $nome = $prefixo . uniqid() . '.' . $ext;
    $destino = __DIR__ . '/../' . $pasta . $nome;

    $dir = dirname($destino);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destino)) {
        return $nome;
    }
    return false;
}
?>
