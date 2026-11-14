<?php
/**
 * Carrinho e Checkout
 */
$titulo = 'Carrinho';
require_once '../includes/header.php';
proteger(['cliente']);

$user = utilizadorAtual();
$carrinho = $_SESSION['carrinho'] ?? [];
$erro = '';
$sucesso = '';

// Remover item
if (isset($_GET['remover'])) {
    $id = (int)$_GET['remover'];
    unset($_SESSION['carrinho'][$id]);
    redirect('cliente/carrinho.php');
}

// Limpar carrinho
if (isset($_GET['limpar'])) {
    $_SESSION['carrinho'] = [];
    redirect('cliente/carrinho.php');
}

// Finalizar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar'])) {
    if (empty($carrinho)) {
        $erro = 'O carrinho está vazio.';
    } else {
        $totalGeral = 0;
        $restauranteId = null;
        foreach ($carrinho as $item) {
            $totalGeral += $item['preco'] * $item['quantidade'];
            $restauranteId = $item['restaurante_id'];
        }

        $forma = $_POST['forma_pagamento'] ?? 'entrega';
        $obs = trim($_POST['observacoes'] ?? '');

        if ($forma === 'carteira' && $user['saldo_carteira'] < $totalGeral) {
            $erro = 'Saldo insuficiente na carteira.';
        } else {
            $comissao = round($totalGeral * 0.10, 2);
            $valorRest = round($totalGeral - $comissao, 2);

            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO pedidos (cliente_id, restaurante_id, valor_total, minha_comissao_10, valor_restaurante_90, forma_pagamento, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user['id'], $restauranteId, $totalGeral, $comissao, $valorRest, $forma, $obs]);
                $pedidoId = $pdo->lastInsertId();

                $stmtItem = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
                foreach ($carrinho as $item) {
                    $stmtItem->execute([$pedidoId, $item['id'], $item['quantidade'], $item['preco']]);
                }

                if ($forma === 'carteira') {
                    $stmt = $pdo->prepare("UPDATE usuarios SET saldo_carteira = saldo_carteira - ? WHERE id = ?");
                    $stmt->execute([$totalGeral, $user['id']]);

                    $stmt = $pdo->prepare("INSERT INTO carteira (usuario_id, tipo, valor, descricao, pedido_id) VALUES (?, 'pagamento', ?, ?, ?)");
                    $stmt->execute([$user['id'], -$totalGeral, "Pagamento pedido #$pedidoId", $pedidoId]);
                }

                // Comissão para o admin (registo)
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE tipo = 'admin' LIMIT 1");
                $stmt->execute();
                $admin = $stmt->fetch();
                if ($admin) {
                    $stmt = $pdo->prepare("INSERT INTO carteira (usuario_id, tipo, valor, descricao, pedido_id) VALUES (?, 'comissao', ?, ?, ?)");
                    $stmt->execute([$admin['id'], $comissao, "Comissão 10% pedido #$pedidoId", $pedidoId]);
                }

                $pdo->commit();
                $_SESSION['carrinho'] = [];
                $sucesso = "Pedido #$pedidoId realizado com sucesso!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $erro = 'Erro ao processar o pedido. Tente novamente.';
            }
        }
    }
}

$totalGeral = 0;
foreach ($carrinho as $item) {
    $totalGeral += $item['preco'] * $item['quantidade'];
}
?>
<body data-base="<?= htmlspecialchars(BASE_URL) ?>">

<div class="container" style="padding-top:30px;">
    <h1 class="secao-titulo"><i class="fas fa-shopping-cart"></i> Carrinho</h1>

    <?php if ($erro): ?><div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
    <?php if ($sucesso): ?>
        <div class="alert alert-sucesso"><?= htmlspecialchars($sucesso) ?></div>
        <p><a href="<?= url('cliente/pedidos.php') ?>" class="btn-primary" style="width:auto;display:inline-block;">Ver Meus Pedidos</a></p>
    <?php elseif (empty($carrinho)): ?>
        <p>O seu carrinho está vazio. <a href="<?= url('index.php') ?>" style="color:var(--laranja);">Ver restaurantes</a></p>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:1fr 350px;gap:30px;align-items:start;" class="carrinho-grid">
            <div style="background:white;padding:20px;border-radius:var(--radius);box-shadow:var(--sombra);">
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Preço</th>
                            <th>Qtd</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($carrinho as $id => $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nome']) ?></td>
                                <td><?= number_format($item['preco'], 2) ?> MT</td>
                                <td><?= $item['quantidade'] ?></td>
                                <td><strong><?= number_format($item['preco'] * $item['quantidade'], 2) ?> MT</strong></td>
                                <td>
                                    <a href="?remover=<?= $id ?>" style="color:var(--erro);" data-confirm="Remover este item?">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="text-align:right;margin-top:15px;">
                    <a href="?limpar=1" style="color:var(--erro);font-size:0.9rem;" data-confirm="Limpar todo o carrinho?">Limpar carrinho</a>
                </div>
                <div style="text-align:right;margin-top:15px;font-size:1.3rem;">
                    <strong>Total: <span style="color:var(--laranja)"><?= number_format($totalGeral, 2) ?> MT</span></strong>
                </div>
            </div>

            <div class="form-container" style="max-width:100%;margin:0;">
                <h3 style="margin-bottom:15px;">Finalizar Pedido</h3>
                <p style="margin-bottom:15px;">Saldo na carteira: <strong><?= number_format($user['saldo_carteira'], 2) ?> MT</strong></p>

                <form method="POST">
                    <div class="form-group">
                        <label>Forma de pagamento</label>
                        <select name="forma_pagamento">
                            <option value="entrega">Pagar na entrega (dinheiro/M-Pesa)</option>
                            <option value="carteira" <?= $user['saldo_carteira'] >= $totalGeral ? '' : 'disabled' ?>>
                                Carteira Virtual <?= $user['saldo_carteira'] < $totalGeral ? '(saldo insuficiente)' : '' ?>
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Observações (opcional)</label>
                        <textarea name="observacoes" rows="2" placeholder="Ex: sem cebola, tocar a campainha..."></textarea>
                    </div>
                    <button type="submit" name="finalizar" class="btn-primary">
                        <i class="fas fa-check"></i> Confirmar Pedido
                    </button>
                </form>
            </div>
        </div>
        <style>
            @media (max-width: 768px) {
                .carrinho-grid { grid-template-columns: 1fr !important; }
            }
        </style>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
