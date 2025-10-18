# API de Controle de Estoque e Vendas

API REST desenvolvida em Laravel para gerenciar um módulo simplificado de controle de estoque e vendas para um ERP.

## 📋 Índice

- [Tecnologias](#tecnologias)
- [Funcionalidades](#funcionalidades)
- [Instalação com Docker](#instalação-com-docker)
- [Instalação Local](#instalação-local)
- [Configuração do Ambiente](#configuração-do-ambiente)
- [Comandos Úteis](#comandos-úteis)
- [Endpoints da API](#endpoints-da-api)
- [Testes](#testes)
- [Troubleshooting](#troubleshooting)
- [Documentação Adicional](#documentação-adicional)

## 🚀 Tecnologias

- **Laravel 12**
- **PHP 8.2**
- **PostgreSQL 16**
- **Docker & Docker Compose**
- **Nginx**
- **PHPUnit** para testes
- **Swagger/OpenAPI** para documentação

## ✨ Funcionalidades

### 1. Gerenciamento de Produtos
- ✅ Listar todos os produtos com informações de estoque
- ✅ Criar novos produtos com validação de dados
- ✅ Consultar detalhes de produtos incluindo inventário
- ✅ Atualizar produtos existentes
- ✅ Deletar produtos
- ✅ Validação de SKU único
- ✅ Validação de preço de venda maior que custo
- ✅ Cálculo automático de margem de lucro e lucro unitário

### 2. Gerenciamento de Estoque
- ✅ Registrar entrada de produtos no estoque
- ✅ Consultar estoque atual com valores totais
- ✅ Calcular lucro projetado
- ✅ Resumo consolidado do inventário (total de itens, unidades, custos e valores)
- ✅ Cálculo de margem de lucro percentual
- ✅ Incremento automático de estoque para produtos existentes

### 3. Processamento de Vendas
- ✅ Registrar vendas com múltiplos itens
- ✅ Cálculo automático de valor total e margem de lucro
- ✅ Consultar detalhes de vendas
- ✅ Atualização automática de estoque via eventos
- ✅ Validação de estoque disponível antes da venda
- ✅ Cálculo de lucro por item e total
- ✅ Evento `SaleCompleted` disparado após venda bem-sucedida

## 🐳 Instalação com Docker (Recomendado)

### Pré-requisitos
- Docker
- Docker Compose

### Serviços Docker
- **app**: Aplicação Laravel (PHP 8.2-FPM)
- **nginx**: Servidor web (Nginx Alpine)
- **db**: Banco de dados PostgreSQL 16
- **pgadmin**: Interface web para gerenciar PostgreSQL (opcional)

### Passo a Passo

#### 1. Clone o repositório
```bash
git clone <repository-url>
cd php-teste-pleno
```

#### 2. Configure as variáveis de ambiente
```bash
cp .env.docker .env
```

#### 3. Construir e iniciar os containers
```bash
docker compose up -d --build
```

Este comando irá:
- Construir a imagem Docker da aplicação
- Iniciar todos os serviços (app, nginx, db, pgadmin)
- Criar o volume para persistência do PostgreSQL

#### 4. Gerar a chave da aplicação
```bash
docker compose exec app php artisan key:generate
```

#### 5. Executar as migrations
```bash
docker compose exec app php artisan migrate
```

#### 6. Popular o banco de dados (opcional)
```bash
docker compose exec app php artisan db:seed
```

#### 7. Instalar dependências do Swagger (opcional)
```bash
docker compose exec app composer require darkaonline/l5-swagger
docker compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
docker compose exec app php artisan l5-swagger:generate
```

### Acessar a Aplicação

- **API**: http://localhost:8000
- **Documentação Swagger**: http://localhost:8000/api/documentation
- **PgAdmin**: http://localhost:5050
  - Email: admin@admin.com
  - Senha: admin

### Configuração do PostgreSQL

**Credenciais padrão:**
- **Host**: db (dentro do Docker) ou localhost (fora do Docker)
- **Porta**: 5432
- **Database**: api_estoque
- **Username**: laravel
- **Password**: secret

**Conectar ao PostgreSQL via PgAdmin:**
1. Acesse http://localhost:5050
2. Faça login com as credenciais acima
3. Adicione um novo servidor:
   - **Host**: db
   - **Port**: 5432
   - **Database**: api_estoque
   - **Username**: laravel
   - **Password**: secret

## 💻 Instalação Local

### Pré-requisitos
- PHP 8.2 ou superior
- Composer
- PostgreSQL 16
- Node.js e NPM

### Passo a Passo

#### 1. Instalar dependências
```bash
composer install
npm install
```

#### 2. Configurar ambiente
```bash
cp .env.example .env
php artisan key:generate
```

#### 3. Configurar banco de dados no `.env`
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=api_estoque
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

#### 4. Executar migrations e seeders
```bash
php artisan migrate
php artisan db:seed
```

#### 5. Iniciar o servidor
```bash
php artisan serve
```

## 📝 Comandos Úteis

### Comandos Docker

#### Ver logs dos containers
```bash
# Todos os serviços
docker compose logs -f

# Apenas a aplicação
docker compose logs -f app

# Apenas o banco de dados
docker compose logs -f db
```

#### Executar comandos Artisan
```bash
docker compose exec app php artisan [comando]
```

**Exemplos:**
```bash
# Criar migration
docker compose exec app php artisan make:migration create_users_table

# Criar controller
docker compose exec app php artisan make:controller UserController

# Limpar cache
docker compose exec app php artisan cache:clear

# Executar seeders
docker compose exec app php artisan db:seed
```

#### Acessar o container
```bash
# Acessar bash do container da aplicação
docker compose exec app bash

# Acessar PostgreSQL CLI
docker compose exec db psql -U laravel -d api_estoque
```

#### Gerenciar containers
```bash
# Parar os containers
docker compose down

# Parar e remover volumes (CUIDADO: apaga o banco de dados)
docker compose down -v

# Reconstruir os containers
docker compose up -d --build --force-recreate
```

#### Ajustar permissões (se necessário)
```bash
docker compose exec app chown -R laravel:www-data /var/www
docker compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

### Comandos com Makefile

Se você tiver o Makefile configurado:
```bash
# Setup completo
make setup

# Executar testes
make test

# Limpar cache
make cache-clear

# Acessar shell do container
make shell

# Acessar PostgreSQL
make db-shell

# Ver logs
make logs

# Reiniciar containers
make restart

# Executar migrations
make migrate

# Popular banco de dados
make seed
```

## 📡 Endpoints da API

### Base URL
```
http://localhost:8000/api/v1
```

Para documentação completa dos endpoints, consulte:
- **Swagger UI**: http://localhost:8000/api/documentation
- **Arquivo**: [API_README.md](./API_README.md)

### Principais Endpoints

- **GET** `/api/v1/products` - Listar produtos
- **POST** `/api/v1/products` - Criar produto
- **GET** `/api/v1/products/{id}` - Detalhes do produto
- **PUT** `/api/v1/products/{id}` - Atualizar produto
- **DELETE** `/api/v1/products/{id}` - Deletar produto
- **GET** `/api/v1/inventory` - Consultar estoque
- **POST** `/api/v1/inventory` - Adicionar ao estoque
- **POST** `/api/v1/sales` - Criar venda
- **GET** `/api/v1/sales/{id}` - Detalhes da venda

## 🧪 Testes

A aplicação possui uma cobertura completa de testes, incluindo testes unitários e de integração.

### Executar todos os testes
```bash
# Com Docker
docker compose exec app php artisan test

# Local
php artisan test
```

### Executar testes específicos
```bash
# Testes unitários
docker compose exec app php artisan test --testsuite=Unit

# Testes de feature
docker compose exec app php artisan test --testsuite=Feature

# Teste específico
docker compose exec app php artisan test --filter=InventoryServiceTest
```

### Cobertura de Testes
```bash
docker compose exec app php artisan test --coverage
```

### Suítes de Testes

#### Testes Unitários (Services)
- **ProductServiceTest**: 10 testes
  - CRUD completo de produtos
  - Validação de relacionamentos
  - Tratamento de erros
  
- **InventoryServiceTest**: 5 testes
  - Adição de estoque
  - Verificação de disponibilidade
  - Cálculos de valores e lucros
  - Resumo consolidado
  
- **SaleServiceTest**: 5 testes
  - Criação de vendas
  - Validação de estoque
  - Cálculos de totais e margens
  - Disparo de eventos

#### Testes de Integração (API)
- **ProductApiTest**: 14 testes
  - Endpoints CRUD completos
  - Validações de dados
  - Tratamento de erros HTTP
  - Integração com inventário
  
- **InventoryApiTest**: 6 testes
  - Adição de estoque via API
  - Consulta de status
  - Validações de entrada
  - Cálculos de resumo
  
- **SaleApiTest**: 8 testes
  - Criação de vendas via API
  - Validação de estoque disponível
  - Atualização automática de inventário
  - Vendas com múltiplos itens

**Total**: 48 testes automatizados

## 🐛 Troubleshooting

### Erro de conexão com o banco de dados

Certifique-se de que:
1. O serviço `db` está rodando: `docker compose ps`
2. As credenciais no `.env` estão corretas
3. O host do banco é `db` (não `localhost` ou `127.0.0.1`)
4. Aguarde o PostgreSQL ficar pronto

O Docker Compose inclui um healthcheck que garante que o PostgreSQL esteja pronto antes de iniciar a aplicação.

### Erro de permissão

Execute:
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Container não inicia

Verifique os logs:
```bash
docker compose logs app
docker compose logs db
```

### Porta já em uso

Se a porta 8000 ou 5432 já estiver em uso, edite o `docker-compose.yml` e altere as portas:

```yaml
ports:
  - "8001:80"  # Altere 8000 para 8001
```

## 🔄 Atualizar a Aplicação

Após fazer alterações no código:

```bash
# Se alterou dependências do Composer
docker compose exec app composer install

# Se alterou dependências do NPM
docker compose exec app npm install
docker compose exec app npm run build

# Se alterou migrations
docker compose exec app php artisan migrate

# Se alterou configurações
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
```

## 📚 Documentação Adicional

- [API_README.md](./API_README.md) - Documentação completa da API com exemplos de requisições e respostas
- [DOCKER_README.md](./DOCKER_README.md) - Documentação detalhada do ambiente Docker
- [Documentação do Laravel](https://laravel.com/docs)
- [Documentação do Docker](https://docs.docker.com/)
- [Documentação do PostgreSQL](https://www.postgresql.org/docs/)

## 🏗️ Estrutura do Projeto

```
app/
├── Events/              # Eventos da aplicação
│   └── SaleCompleted.php
├── Listeners/           # Listeners de eventos
│   └── UpdateInventoryOnSale.php
├── Models/              # Models Eloquent
│   ├── Product.php
│   ├── Inventory.php
│   └── Sale.php
├── Services/            # Camada de serviços (lógica de negócio)
│   ├── ProductService.php
│   ├── InventoryService.php
│   └── SaleService.php
├── Http/
│   ├── Controllers/Api/ # Controllers da API
│   │   ├── ProductController.php
│   │   ├── InventoryController.php
│   │   └── SaleController.php
│   └── Requests/        # Form Requests (validação)
│       ├── CreateProductRequest.php
│       ├── UpdateProductRequest.php
│       ├── AddInventoryRequest.php
│       └── CreateSaleRequest.php
└── Providers/           # Service Providers
    └── EventServiceProvider.php

database/
├── migrations/          # Migrations do banco
├── seeders/            # Seeders
└── factories/          # Factories para testes

tests/
├── Unit/               # Testes unitários (Services)
│   ├── ProductServiceTest.php
│   ├── InventoryServiceTest.php
│   └── SaleServiceTest.php
└── Feature/            # Testes de integração (API)
    ├── ProductApiTest.php
    ├── InventoryApiTest.php
    └── SaleApiTest.php
```

## 🎯 Arquitetura e Padrões

### Camadas da Aplicação

1. **Controllers (API Layer)**
   - Recebem requisições HTTP
   - Validam dados via Form Requests
   - Delegam lógica de negócio para Services
   - Retornam respostas JSON padronizadas

2. **Services (Business Logic Layer)**
   - Contêm toda a lógica de negócio
   - Interagem com Models
   - Disparam eventos quando necessário
   - Isolam regras de negócio dos controllers

3. **Models (Data Layer)**
   - Representam entidades do banco de dados
   - Definem relacionamentos
   - Contêm accessors e mutators

4. **Events & Listeners**
   - `SaleCompleted`: Disparado após criação de venda
   - `UpdateInventoryOnSale`: Atualiza estoque automaticamente

### Padrões Utilizados

- **Service Layer Pattern**: Separação da lógica de negócio
- **Repository Pattern** (via Eloquent): Abstração de acesso a dados
- **Event-Driven Architecture**: Desacoplamento via eventos
- **Form Request Validation**: Validação centralizada
- **Dependency Injection**: Injeção de dependências via constructor
- **RESTful API**: Endpoints seguindo convenções REST

### Regras de Negócio e Validações

#### Produtos
- SKU deve ser único no sistema
- Preço de venda deve ser maior que o preço de custo
- Campos obrigatórios: `sku`, `name`, `cost_price`, `sale_price`
- Margem de lucro calculada automaticamente: `((sale_price - cost_price) / cost_price) * 100`
- Lucro unitário calculado: `sale_price - cost_price`

#### Inventário
- Quantidade deve ser um número positivo (maior que zero)
- Product ID deve corresponder a um produto existente
- Ao adicionar estoque para produto existente, a quantidade é incrementada
- Cálculos automáticos:
  - `total_cost = quantity * product.cost_price`
  - `total_value = quantity * product.sale_price`
  - `projected_profit = total_value - total_cost`

#### Vendas
- Deve conter pelo menos um item
- Todos os produtos devem existir no sistema
- Estoque deve ser suficiente para todos os itens
- Atualização de estoque é automática via evento `SaleCompleted`
- Cálculos automáticos por item:
  - `subtotal = quantity * product.sale_price`
  - `cost = quantity * product.cost_price`
  - `profit = subtotal - cost`
- Cálculos totais da venda:
  - `total_amount = soma de todos os subtotais`
  - `total_cost = soma de todos os custos`
  - `total_profit = total_amount - total_cost`
  - `profit_margin = (total_profit / total_amount) * 100`

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT.
