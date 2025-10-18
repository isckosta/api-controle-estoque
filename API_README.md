# API de Controle de Estoque e Vendas

API REST desenvolvida em Laravel para gerenciar um módulo simplificado de controle de estoque e vendas para um ERP.

## 📋 Índice

- [Tecnologias](#tecnologias)
- [Funcionalidades](#funcionalidades)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Instalação](#instalação)
- [Endpoints da API](#endpoints-da-api)
- [Testes](#testes)
- [Documentação](#documentação)

## 🚀 Tecnologias

- **Laravel 12**
- **PHP 8.2**
- **PostgreSQL 16**
- **PHPUnit** para testes
- **Swagger/OpenAPI** para documentação
- **Docker** para containerização

## ✨ Funcionalidades

### 1. Gerenciamento de Produtos
- ✅ Listar todos os produtos
- ✅ Criar novos produtos
- ✅ Consultar detalhes de produtos
- ✅ Atualizar produtos existentes
- ✅ Deletar produtos

### 2. Gerenciamento de Estoque
- ✅ Registrar entrada de produtos no estoque
- ✅ Consultar estoque atual com valores totais
- ✅ Calcular lucro projetado

### 3. Processamento de Vendas
- ✅ Registrar vendas com múltiplos itens
- ✅ Cálculo automático de valor total e margem de lucro
- ✅ Consultar detalhes de vendas
- ✅ Atualização automática de estoque via eventos

## 📁 Estrutura do Projeto

```
app/
├── Events/
│   └── SaleCompleted.php              # Evento disparado ao finalizar venda
├── Listeners/
│   └── UpdateInventoryOnSale.php      # Listener que atualiza estoque
├── Models/
│   ├── Product.php                    # Modelo de Produto
│   ├── Inventory.php                  # Modelo de Inventário
│   ├── Sale.php                       # Modelo de Venda
│   └── SaleItem.php                   # Modelo de Item de Venda
├── Services/
│   ├── InventoryService.php           # Lógica de negócio do estoque
│   └── SaleService.php                # Lógica de negócio de vendas
├── Http/
│   ├── Controllers/Api/
│   │   ├── ProductController.php      # Controller de produtos
│   │   ├── InventoryController.php    # Controller de estoque
│   │   └── SaleController.php         # Controller de vendas
│   └── Requests/
│       ├── CreateProductRequest.php   # Validação de criação de produto
│       ├── UpdateProductRequest.php   # Validação de atualização de produto
│       ├── AddInventoryRequest.php    # Validação de entrada de estoque
│       └── CreateSaleRequest.php      # Validação de criação de venda
└── Providers/
    └── EventServiceProvider.php       # Registro de eventos

database/
├── migrations/                        # Migrations do banco de dados
├── seeders/
│   ├── ProductSeeder.php             # Dados de teste de produtos
│   └── InventorySeeder.php           # Dados de teste de estoque
└── factories/
    └── ProductFactory.php            # Factory para testes

tests/
├── Unit/
│   ├── InventoryServiceTest.php      # Testes unitários do estoque
│   └── SaleServiceTest.php           # Testes unitários de vendas
└── Feature/
    ├── ProductApiTest.php            # Testes de integração de produtos
    ├── InventoryApiTest.php          # Testes de integração do estoque
    └── SaleApiTest.php               # Testes de integração de vendas
```

## 🔧 Instalação

### Usando Docker (Recomendado)

1. **Clone o repositório**
```bash
git clone <repository-url>
cd php-teste-pleno
```

2. **Configure e inicie o ambiente**
```bash
make setup
```

Ou manualmente:
```bash
cp .env.docker .env
docker compose up -d --build
make wait-db
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

3. **Instalar dependências do Swagger**
```bash
docker compose exec app composer require darkaonline/l5-swagger
docker compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
docker compose exec app php artisan l5-swagger:generate
```

### Instalação Local

1. **Instalar dependências**
```bash
composer install
npm install
```

2. **Configurar ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Configurar banco de dados no `.env`**
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=api_estoque
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

4. **Executar migrations e seeders**
```bash
php artisan migrate
php artisan db:seed
```

## 📡 Endpoints da API

### Base URL
```
http://localhost:8000/api/v1
```

### Products (Produtos)

#### GET /products
Listar todos os produtos

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "sku": "LAPTOP-001",
            "name": "Notebook Dell Inspiron 15",
            "description": "Notebook Dell Inspiron 15, Intel Core i7...",
            "cost_price": "3500.00",
            "sale_price": "4999.00",
            "profit_margin": 29.99,
            "unit_profit": "1499.00",
            "stock_quantity": 15,
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T10:00:00.000000Z"
        }
    ]
}
```

#### POST /products
Criar um novo produto

**Request:**
```json
{
    "sku": "WEBCAM-001",
    "name": "Webcam Logitech C920",
    "description": "Webcam Full HD 1080p com microfone estéreo",
    "cost_price": 300.00,
    "sale_price": 499.00
}
```

**Response (201):**
```json
{
    "message": "Product created successfully",
    "data": {
        "id": 6,
        "sku": "WEBCAM-001",
        "name": "Webcam Logitech C920",
        "description": "Webcam Full HD 1080p com microfone estéreo",
        "cost_price": "300.00",
        "sale_price": "499.00",
        "profit_margin": 39.88,
        "unit_profit": "199.00",
        "created_at": "2024-01-01T10:00:00.000000Z"
    }
}
```

#### GET /products/{id}
Obter detalhes de um produto específico

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "sku": "LAPTOP-001",
        "name": "Notebook Dell Inspiron 15",
        "description": "Notebook Dell Inspiron 15, Intel Core i7...",
        "cost_price": "3500.00",
        "sale_price": "4999.00",
        "profit_margin": 29.99,
        "unit_profit": "1499.00",
        "inventory": {
            "quantity": 15,
            "total_cost": "52500.00",
            "total_value": "74985.00",
            "projected_profit": "22485.00",
            "last_updated": "2024-01-01T10:00:00.000000Z"
        },
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:00:00.000000Z"
    }
}
```

#### PUT /products/{id}
Atualizar um produto existente

**Request:**
```json
{
    "name": "Webcam Logitech C920 Pro",
    "sale_price": 549.00
}
```

**Response (200):**
```json
{
    "message": "Product updated successfully",
    "data": {
        "id": 6,
        "sku": "WEBCAM-001",
        "name": "Webcam Logitech C920 Pro",
        "description": "Webcam Full HD 1080p com microfone estéreo",
        "cost_price": "300.00",
        "sale_price": "549.00",
        "profit_margin": 45.36,
        "unit_profit": "249.00",
        "updated_at": "2024-01-01T10:05:00.000000Z"
    }
}
```

#### DELETE /products/{id}
Deletar um produto

**Response (200):**
```json
{
    "message": "Product deleted successfully"
}
```

### Inventory (Estoque)

#### POST /inventory
Registrar entrada de produtos no estoque

**Request:**
```json
{
    "product_id": 1,
    "quantity": 50
}
```

**Response (201):**
```json
{
    "message": "Inventory updated successfully",
    "data": {
        "product_id": 1,
        "product_name": "Notebook Dell Inspiron 15",
        "quantity": 50,
        "last_updated": "2024-01-01T10:00:00.000000Z"
    }
}
```

#### GET /inventory
Obter situação atual do estoque

**Response (200):**
```json
{
    "data": [
        {
            "product_id": 1,
            "sku": "LAPTOP-001",
            "name": "Notebook Dell Inspiron 15",
            "quantity": 50,
            "cost_price": "3500.00",
            "sale_price": "4999.00",
            "total_cost": "175000.00",
            "total_value": "249950.00",
            "projected_profit": "74950.00",
            "last_updated": "2024-01-01T10:00:00.000000Z"
        }
    ],
    "summary": {
        "total_items": 5,
        "total_units": 155,
        "total_cost": "295000.00",
        "total_value": "442945.00",
        "projected_profit": "147945.00",
        "profit_margin": 33.4
    }
}
```

### Sales (Vendas)

#### POST /sales
Registrar uma nova venda

**Request:**
```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        },
        {
            "product_id": 2,
            "quantity": 1
        }
    ]
}
```

**Response (201):**
```json
{
    "message": "Sale created successfully",
    "data": {
        "id": 1,
        "total_amount": "10547.00",
        "total_cost": "7350.00",
        "total_profit": "3197.00",
        "profit_margin": 30.31,
        "status": "completed",
        "items": [
            {
                "product_id": 1,
                "product_name": "Notebook Dell Inspiron 15",
                "quantity": 2,
                "unit_price": "4999.00",
                "subtotal": "9998.00",
                "profit": "2998.00"
            },
            {
                "product_id": 2,
                "product_name": "Mouse Logitech MX Master 3",
                "quantity": 1,
                "unit_price": "549.00",
                "subtotal": "549.00",
                "profit": "199.00"
            }
        ],
        "created_at": "2024-01-01T10:00:00.000000Z"
    }
}
```

#### GET /sales/{id}
Obter detalhes de uma venda específica

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "total_amount": "10547.00",
        "total_cost": "7350.00",
        "total_profit": "3197.00",
        "profit_margin": 30.31,
        "status": "completed",
        "items": [
            {
                "id": 1,
                "product_id": 1,
                "product_name": "Notebook Dell Inspiron 15",
                "product_sku": "LAPTOP-001",
                "quantity": 2,
                "unit_price": "4999.00",
                "unit_cost": "3500.00",
                "subtotal": "9998.00",
                "total_cost": "7000.00",
                "profit": "2998.00"
            }
        ],
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:00:00.000000Z"
    }
}
```

## 🧪 Testes

### Executar todos os testes
```bash
# Com Docker
make test

# Ou
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

## 📚 Documentação

### Swagger/OpenAPI

Após instalar e configurar o l5-swagger:

1. **Gerar documentação**
```bash
docker compose exec app php artisan l5-swagger:generate
```

2. **Acessar documentação**
```
http://localhost:8000/api/documentation
```

### Postman Collection

Importe o arquivo `postman_collection.json` no Postman para testar todos os endpoints.

**Variáveis de ambiente:**
- `base_url`: http://localhost:8000

## 🗄️ Estrutura do Banco de Dados

### products
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID do produto |
| sku | string | Código único do produto |
| name | string | Nome do produto |
| description | text | Descrição do produto |
| cost_price | decimal(10,2) | Preço de custo |
| sale_price | decimal(10,2) | Preço de venda |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

### inventory
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID do inventário |
| product_id | bigint | ID do produto |
| quantity | integer | Quantidade em estoque |
| last_updated | timestamp | Última atualização |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

### sales
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID da venda |
| total_amount | decimal(10,2) | Valor total da venda |
| total_cost | decimal(10,2) | Custo total |
| total_profit | decimal(10,2) | Lucro total |
| status | enum | Status (pending, completed, cancelled) |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

### sale_items
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID do item |
| sale_id | bigint | ID da venda |
| product_id | bigint | ID do produto |
| quantity | integer | Quantidade vendida |
| unit_price | decimal(10,2) | Preço unitário |
| unit_cost | decimal(10,2) | Custo unitário |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

## 🎯 Dados de Teste

A aplicação inclui 5 produtos pré-cadastrados:

1. **Notebook Dell Inspiron 15** (SKU: LAPTOP-001)
   - Custo: R$ 3.500,00
   - Venda: R$ 4.999,00
   - Estoque inicial: 15 unidades

2. **Mouse Logitech MX Master 3** (SKU: MOUSE-001)
   - Custo: R$ 350,00
   - Venda: R$ 549,00
   - Estoque inicial: 50 unidades

3. **Teclado Mecânico Keychron K2** (SKU: KEYBOARD-001)
   - Custo: R$ 450,00
   - Venda: R$ 699,00
   - Estoque inicial: 30 unidades

4. **Monitor LG UltraWide 29"** (SKU: MONITOR-001)
   - Custo: R$ 1.200,00
   - Venda: R$ 1.799,00
   - Estoque inicial: 20 unidades

5. **Headset HyperX Cloud II** (SKU: HEADSET-001)
   - Custo: R$ 400,00
   - Venda: R$ 599,00
   - Estoque inicial: 40 unidades

## 🏗️ Arquitetura e Boas Práticas

### Padrões Implementados

- **Service Layer Pattern**: Lógica de negócio separada em services
- **Repository Pattern**: Através dos Eloquent Models
- **Event-Driven Architecture**: Eventos para atualização de estoque
- **Request Validation**: Form Requests para validação
- **Dependency Injection**: Injeção de dependências nos controllers
- **RESTful API**: Endpoints seguindo padrões REST

### Escalabilidade

- **Queue Support**: Listener implementa `ShouldQueue` para processamento assíncrono
- **Database Indexing**: Índices em campos frequentemente consultados
- **Eager Loading**: Uso de `with()` para evitar N+1 queries
- **Caching Ready**: Estrutura preparada para implementação de cache
- **Horizontal Scaling**: Stateless API pronta para múltiplas instâncias

### Segurança

- **Mass Assignment Protection**: `$fillable` em todos os models
- **SQL Injection Prevention**: Uso de Eloquent ORM
- **Validation**: Validação rigorosa de inputs
- **Error Handling**: Tratamento adequado de exceções

## 🔄 Eventos e Listeners

### SaleCompleted Event
Disparado quando uma venda é finalizada.

**Listener: UpdateInventoryOnSale**
- Atualiza automaticamente o estoque
- Registra logs da operação
- Executa de forma assíncrona (queue)

## 📝 Comandos Úteis

```bash
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

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT.
