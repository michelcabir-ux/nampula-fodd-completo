<?php
/**
 * Cabeçalho comum a todas as páginas
 */
require_once __DIR__ . '/auth.php';
$user = utilizadorAtual();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo) ? htmlspecialchars($titulo) . ' | ' : ''; ?>Nampula Fodd</title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container nav-content">
            <a href="<?= url('index.php') ?>" class="logo">
                <i class="fas fa-utensils"></i> Nampula <span>Fodd</span>
            </a>

            <button class="menu-toggle" id="menuToggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="nav-links" id="navLinks">
                <li><a href="<?= url('index.php') ?>">Início</a></li>
                <li><a href="<?= url('restaurantes.php') ?>">Restaurantes</a></li>

                <?php if ($user): ?>
                    <?php if ($user['tipo'] === 'cliente'): ?>
                        <li><a href="<?= url('cliente/carrinho.php') ?>"><i class="fas fa-shopping-cart"></i> Carrinho</a></li>
                        <li><a href="<?= url('cliente/carteira.php') ?>"><i class="fas fa-wallet"></i> Carteira</a></li>
                        <li><a href="<?= url('cliente/pedidos.php') ?>">Meus Pedidos</a></li>
                    <?php elseif ($user['tipo'] === 'restaurante'): ?>
                        <li><a href="<?= url('restaurante/dashboard.php') ?>">Dashboard</a></li>
                        <li><a href="<?= url('restaurante/cardapio.php') ?>">Cardápio</a></li>
                    <?php elseif ($user['tipo'] === 'admin'): ?>
                        <li><a href="<?= url('admin/dashboard.php') ?>">Admin</a></li>
                    <?php endif; ?>
                    <li class="user-menu">
                        <span>Olá, <?= htmlspecialchars($user['nome']) ?></span>
                        <a href="<?= url('logout.php') ?>" class="btn-sair">Sair</a>
                    </li>
                <?php else: ?>
                    <li><a href="<?= url('login.php') ?>" class="btn-login">Entrar</a></li>
                    <li><a href="<?= url('registo.php') ?>" class="btn-registo">Criar Conta</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
