<?php
/**
 * Funções de autenticação e verificação de permissões
 */

require_once __DIR__ . '/../config/database.php';

function estaLogado() {
    return isset($_SESSION['usuario_id']);
}

function utilizadorAtual() {
    global $pdo;
    if (!estaLogado()) return null;

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    return $stmt->fetch();
}

function temPermissao($tipo) {
    $user = utilizadorAtual();
    return $user && $user['tipo'] === $tipo;
}

function proteger($tiposPermitidos = []) {
    if (!estaLogado()) {
        redirect('login.php');
    }
    if (!empty($tiposPermitidos)) {
        $user = utilizadorAtual();
        if (!in_array($user['tipo'], $tiposPermitidos)) {
            redirect('index.php');
        }
    }
}

function fazerLogin($email, $senha) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['usuario_id']   = $user['id'];
        $_SESSION['usuario_nome'] = $user['nome'];
        $_SESSION['usuario_tipo'] = $user['tipo'];
        return true;
    }
    return false;
}

function fazerLogout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    redirect('index.php');
}
?>
