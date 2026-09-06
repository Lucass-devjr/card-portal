# Card Portal

Portal administrativo para gestão de cartas colecionáveis (Magic: The Gathering, Pokémon e Yu-Gi-Oh!).

## Stack

- **Backend:** PHP puro (sem frameworks)
- **Frontend:** HTML5 + CSS3 + JavaScript Vanilla (sem libs)
- **Banco:** MySQL via XAMPP

## Pré-requisitos

- PHP 8.x instalado e no PATH
- XAMPP com MySQL rodando na porta 3306

## Como rodar

### 1. Iniciar o MySQL

Abra o XAMPP Control Panel e clique em Start no MySQL.

### 2. Criar o banco e rodar o schema

Acesse `http://localhost/phpmyadmin`
Crie um banco chamado: `card_portal`
Clique em SQL e cole o conteúdo de `database/schema.sql` e execute.

### 3. Configurar o .env

Crie um arquivo `.env` na raiz com:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=card_portal
DB_USER=root
DB_PASSWORD=
```

### 4. Iniciar o servidor PHP

```bash
cd card-portal
php -S localhost:8000 -t .
```

### 5. Acessar

`http://localhost:8000/frontend/index.html`

## Credenciais de teste

| Usuário | Senha    |
|---------|----------|
| admin   | admin123 |

## Estrutura do projeto

```
card-portal/
├── backend/
│   ├── config/database.php      # Conexão PDO com MySQL
│   ├── controllers/
│   │   ├── AuthController.php   # Login/logout com session PHP
│   │   └── CardController.php   # CRUD de cartas
│   ├── middleware/auth.php       # Verificação de sessão
│   └── index.php                # Roteador simples
├── frontend/
│   ├── index.html               # Página de login
│   ├── dashboard.html           # Listagem e gerenciamento
│   ├── css/style.css            # Estilos completos
│   └── js/
│       ├── auth.js              # Lógica de autenticação
│       ├── cards.js             # CRUD frontend + filtros
│       └── editions.js          # Edições e raridades por jogo
├── database/
│   └── schema.sql               # DDL + seed
└── README.md
```

## API Endpoints

| Método | Rota               | Descrição           | Auth |
|--------|--------------------|--------------------|------|
| POST   | /api/login         | Login               | Não  |
| POST   | /api/logout        | Logout              | Sim  |
| GET    | /api/me            | Usuário logado      | Sim  |
| GET    | /api/cards         | Listar cartas       | Sim  |
| GET    | /api/cards/:id     | Detalhe de uma carta| Sim  |
| POST   | /api/cards         | Criar carta         | Sim  |
| PUT    | /api/cards/:id     | Editar carta        | Sim  |
| DELETE | /api/cards/:id     | Excluir carta       | Sim  |

## Decisões de UX/Produto

### 1. Selects dinâmicos de edição por Card Game
Ao selecionar o Card Game, o campo de Edição é desabilitado, dispara uma busca (simulada localmente em `editions.js`) e exibe um loading antes de popular as opções.
Isso garante que apenas edições válidas do jogo escolhido sejam selecionadas, evitando erros de cadastro e melhorando a experiência de usuários menos técnicos.

### 2. Fundo animado na tela de login
A tela de login possui linhas neon animadas em CSS puro percorrendo o grid do fundo, criando uma atmosfera temática de card game sem comprometer a performance.
A decisão foi usar animações CSS puras (`@keyframes`) sem JavaScript, garantindo fluidez mesmo em máquinas mais simples.
