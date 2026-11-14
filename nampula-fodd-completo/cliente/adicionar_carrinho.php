<?php
/**
 * Adicionar produto ao carrinho (AJAX / POST)
 */
require_once '../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

if (!estaLogado() || !temPermissao('cliente')) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Faça login como cliente.']);
    exit;
}

$produtoId = (int)($_POST['produto_id'] ?? 0);
$quantidade = max(1, (int)($_POST['quantidade'] ?? 1));

if (!$produtoId) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Produto inválido.']);
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, r.aberto, r.activo 
    FROM produtos p 
    JOIN restaurantes r ON r.id = p.restaurante_id 
    WHERE p.id = ? AND p.activo = 1");
$stmt->execute([$produtoId]);
$produto = $stmt->fetch();

if (!$produto || !$produto['activo'] || !$produto['aberto']) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Produto indisponível ou restaurante fechado.']);
    exit;
}

// Preço com desconto se for prato do dia
$preco = $produto['preco'];
if ($produto['prato_do_dia'] && $produto['desconto_prato_dia'] > 0) {
    $preco = $preco * (1 - $produto['desconto_prato_dia'] / 100);
}

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Se já existe, aumenta quantidade
if (isset($_SESSION['carrinho'][$produtoId])) {
    $_SESSION['carrinho'][$produtoId]['quantidade'] += $quantidade;
} else {
    $_SESSION['carrinho'][$produtoId] = [
        'id'             => $produto['id'],
        'nome'           => $produto['nome'],
        'preco'          => round($preco, 2),
        'quantidade'     => $quantidade,
        'restaurante_id' => $produto['restaurante_id']
    ];
}

// Só permite produtos do mesmo restaurante
$restIds = array_unique(array_column($_SESSION['carrinho'], 'restaurante_id'));
if (count($restIds) > 1) {
    // Remove os de outros restaurantes
    foreach ($_SESSION['carrinho'] as $pid => $item) {
        if ($item['restaurante_id'] != $produto['restaurante_id']) {
            unset($_SESSION['carrinho'][$pid]);
        }
    }
}

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Adicionado ao carrinho!',
    'total_itens' => array_sum(array_column($_SESSION['carrinho'], 'quantidade'))
]);
