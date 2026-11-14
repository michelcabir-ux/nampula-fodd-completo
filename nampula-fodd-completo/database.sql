-- =====================================================
-- NAMPULA FODD - Banco de Dados MySQL (versão completa)
-- iFood de Nampula e Nacala
-- =====================================================

CREATE DATABASE IF NOT EXISTS nampula_fodd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nampula_fodd;

-- -----------------------------------------------------
-- Tabela: usuarios
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('cliente', 'restaurante', 'admin') NOT NULL DEFAULT 'cliente',
    saldo_carteira DECIMAL(10,2) DEFAULT 0.00,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabela: restaurantes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    cidade ENUM('Nampula', 'Nacala-Porto', 'Nacala-a-Velha') NOT NULL,
    tipo_comida VARCHAR(100),
    endereco VARCHAR(255),
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT 'default-rest.jpg',
    aberto TINYINT(1) DEFAULT 1,
    activo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabela: produtos (cardápio)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(80) DEFAULT 'Geral',
    foto VARCHAR(255) DEFAULT 'default-prato.jpg',
    prato_do_dia TINYINT(1) DEFAULT 0,
    desconto_prato_dia DECIMAL(5,2) DEFAULT 0.00,
    activo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabela: pedidos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    restaurante_id INT NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    minha_comissao_10 DECIMAL(10,2) NOT NULL,
    valor_restaurante_90 DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'aceito', 'recusado', 'em_preparo', 'entregue', 'cancelado') DEFAULT 'pendente',
    forma_pagamento ENUM('carteira', 'entrega') DEFAULT 'entrega',
    status_saque ENUM('nao_solicitado', 'solicitado', 'pago') DEFAULT 'nao_solicitado',
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabela: itens_pedido
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabela: comentarios
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    restaurante_id INT NOT NULL,
    pedido_id INT NOT NULL,
    nota TINYINT NOT NULL,
    texto TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tabela: carteira (histórico de movimentos)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS carteira (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('deposito', 'pagamento', 'comissao', 'saque') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    descricao VARCHAR(255),
    pedido_id INT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Dados iniciais de demonstração
-- Senha de TODAS as contas: password
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- -----------------------------------------------------

INSERT INTO usuarios (nome, email, telefone, senha, tipo, saldo_carteira) VALUES
('Admin Nampula Fodd', 'admin@nampulafodd.co.mz', '840000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0.00),
('João Cliente', 'cliente@teste.com', '841111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', 500.00),
('Maria Restaurante', 'maria@restaurante.com', '842222222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'restaurante', 0.00),
('Carlos Pizza', 'carlos@pizza.com', '843333333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'restaurante', 0.00);

INSERT INTO restaurantes (usuario_id, nome, descricao, cidade, tipo_comida, endereco, latitude, longitude, foto, aberto, activo) VALUES
(3, 'Churrasqueira Nampula', 'Os melhores grelhados da cidade. Frango, carne e peixe.', 'Nampula', 'Moçambicana', 'Av. Eduardo Mondlane, Nampula', -15.11670000, 39.26670000, 'default-rest.jpg', 1, 1),
(4, 'Pizza Nacala', 'Pizzas artesanais e fast food. Entrega rápida.', 'Nacala-Porto', 'Pizza', 'Rua Principal, Nacala-Porto', -14.54270000, 40.67290000, 'default-rest.jpg', 1, 1);

INSERT INTO produtos (restaurante_id, nome, descricao, preco, categoria, foto, prato_do_dia, desconto_prato_dia, activo) VALUES
(1, 'Frango Grelhado Completo', 'Frango inteiro grelhado com batata e salada', 450.00, 'Pratos Principais', 'default-prato.jpg', 1, 10.00, 1),
(1, 'Matapa com Camarão', 'Matapa tradicional com camarão fresco', 380.00, 'Pratos Principais', 'default-prato.jpg', 0, 0, 1),
(1, 'Sumo de Manga', 'Sumo natural de manga', 80.00, 'Bebidas', 'default-prato.jpg', 0, 0, 1),
(1, 'Caril de Camarão', 'Caril de camarão com arroz', 420.00, 'Pratos Principais', 'default-prato.jpg', 0, 0, 1),
(2, 'Pizza Margherita', 'Molho de tomate, mozzarella e manjericão', 350.00, 'Pizzas', 'default-prato.jpg', 1, 15.00, 1),
(2, 'Pizza Pepperoni', 'Pepperoni e queijo mozzarella', 420.00, 'Pizzas', 'default-prato.jpg', 0, 0, 1),
(2, 'Hambúrguer Clássico', 'Carne, alface, tomate e queijo', 280.00, 'Fast Food', 'default-prato.jpg', 0, 0, 1),
(2, 'Refrigerante 500ml', 'Coca-Cola, Fanta ou Sprite', 60.00, 'Bebidas', 'default-prato.jpg', 0, 0, 1);
