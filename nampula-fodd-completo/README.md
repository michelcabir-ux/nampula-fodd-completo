# 🍔 Nampula Fodd
### O iFood de Nampula e Nacala — Versão Completa

Plataforma web completa de delivery de comida desenvolvida com **HTML, CSS, JavaScript, PHP e MySQL**.

---

## ✅ O que foi adicionado / corrigido nesta versão

- [x] Página `restaurantes.php` (listagem pública com pesquisa e GPS)
- [x] Upload de fotos (restaurantes e pratos)
- [x] Gestão de restaurantes no Admin (activar/desactivar, abrir/fechar)
- [x] Sistema de avaliações completo (só depois de pedido entregue)
- [x] Caminhos dinâmicos (funciona com qualquer nome de pasta)
- [x] 2 restaurantes de demonstração com produtos
- [x] Toggle aberto/fechado no dashboard do restaurante
- [x] Comissão 10% automática + gestão de saques

---

## 📁 Estrutura

```
nampula-fodd/
├── admin/
│   ├── dashboard.php
│   ├── carteira.php
│   ├── pedidos.php
│   └── restaurantes.php      ← NOVO
├── cliente/
│   ├── carrinho.php
│   ├── carteira.php
│   ├── pedidos.php
│   └── adicionar_carrinho.php
├── restaurante/
│   ├── dashboard.php
│   ├── cardapio.php          ← com upload
│   └── criar_restaurante.php ← com upload
├── config/database.php
├── includes/
├── css/style.css
├── js/main.js
├── uploads/
├── database.sql
├── index.php
├── restaurantes.php          ← NOVO
├── restaurante.php
├── login.php
├── registo.php
└── logout.php
```

---

## 🚀 Como instalar (XAMPP / Laragon / Hosting)

### 1. Base de dados
1. Abra o **phpMyAdmin** (`http://localhost/phpmyadmin`)
2. Crie a base de dados ou importe directamente o ficheiro `database.sql`
3. Em phpMyAdmin → Importar → escolha `database.sql` → Executar

### 2. Configuração
Abra `config/database.php` e ajuste se necessário:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // XAMPP: root
define('DB_PASS', '');          // XAMPP: vazio
define('DB_NAME', 'nampula_fodd');
```

### 3. Pasta do projecto
- **XAMPP**: copie a pasta para `C:\xampp\htdocs\`
- **Laragon**: copie para `C:\laragon\www\`

### 4. Permissões
A pasta `uploads/` precisa de permissão de escrita (chmod 755 ou 777).

### 5. Abrir no navegador
```
http://localhost/nampula-fodd-completo/
```
(ou o nome da pasta que usou)

---

## 👤 Contas de Demonstração

| Tipo        | Email                      | Senha    |
|-------------|----------------------------|----------|
| **Admin**   | admin@nampulafodd.co.mz    | password |
| **Cliente** | cliente@teste.com          | password |
| **Restaurante** | maria@restaurante.com  | password |
| **Restaurante 2** | carlos@pizza.com     | password |

---

## 💰 Monetização

1. Cliente faz pedido → valor total entra no sistema  
2. Sistema retém automaticamente **10%** de comissão (ADMIN)  
3. Restaurante fica com **90%** e pode solicitar saque  
4. Admin → **Minha Carteira** gere os saques  

---

## 🛠️ Tecnologias

- HTML5 + CSS3 (Flexbox, Grid, variáveis)
- JavaScript (DOM, Fetch, Geolocalização)
- PHP 7+ (PDO, Sessões, password_hash, upload)
- MySQL (relações, agregações)

**Sem React, Node, Laravel ou frameworks.**

---

Desenvolvido como trabalho final académico.  
Nampula & Nacala ❤️
