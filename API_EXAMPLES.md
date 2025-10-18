# Exemplos de Uso da API

Este documento contém exemplos práticos de como usar a API de Controle de Estoque e Vendas.

## 📋 Pré-requisitos

Certifique-se de que a API está rodando:
```bash
make setup
```

## 🔧 Testando com cURL

### 1. Adicionar Estoque

```bash
curl -X POST http://localhost:8000/api/v1/inventory \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "product_id": 1,
    "quantity": 50
  }'
```

**Resposta esperada:**
```json
{
  "message": "Inventory updated successfully",
  "data": {
    "product_id": 1,
    "product_name": "Notebook Dell Inspiron 15",
    "quantity": 65,
    "last_updated": "2024-01-17T19:30:00.000000Z"
  }
}
```

### 2. Consultar Estoque

```bash
curl -X GET http://localhost:8000/api/v1/inventory \
  -H "Accept: application/json"
```

**Resposta esperada:**
```json
{
  "data": [
    {
      "product_id": 1,
      "sku": "LAPTOP-001",
      "name": "Notebook Dell Inspiron 15",
      "quantity": 65,
      "cost_price": "3500.00",
      "sale_price": "4999.00",
      "total_cost": "227500.00",
      "total_value": "324935.00",
      "projected_profit": "97435.00",
      "last_updated": "2024-01-17T19:30:00.000000Z"
    },
    {
      "product_id": 2,
      "sku": "MOUSE-001",
      "name": "Mouse Logitech MX Master 3",
      "quantity": 50,
      "cost_price": "350.00",
      "sale_price": "549.00",
      "total_cost": "17500.00",
      "total_value": "27450.00",
      "projected_profit": "9950.00",
      "last_updated": "2024-01-17T19:00:00.000000Z"
    }
  ],
  "summary": {
    "total_items": 5,
    "total_units": 165,
    "total_cost": "312500.00",
    "total_value": "467935.00",
    "projected_profit": "155435.00",
    "profit_margin": 33.21
  }
}
```

### 3. Criar Venda (Item Único)

```bash
curl -X POST http://localhost:8000/api/v1/sales \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 2
      }
    ]
  }'
```

**Resposta esperada:**
```json
{
  "message": "Sale created successfully",
  "data": {
    "id": 1,
    "total_amount": "9998.00",
    "total_cost": "7000.00",
    "total_profit": "2998.00",
    "profit_margin": 29.99,
    "status": "completed",
    "items": [
      {
        "product_id": 1,
        "product_name": "Notebook Dell Inspiron 15",
        "quantity": 2,
        "unit_price": "4999.00",
        "subtotal": "9998.00",
        "profit": "2998.00"
      }
    ],
    "created_at": "2024-01-17T19:35:00.000000Z"
  }
}
```

### 4. Criar Venda (Múltiplos Itens)

```bash
curl -X POST http://localhost:8000/api/v1/sales \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 1
      },
      {
        "product_id": 2,
        "quantity": 2
      },
      {
        "product_id": 5,
        "quantity": 1
      }
    ]
  }'
```

**Resposta esperada:**
```json
{
  "message": "Sale created successfully",
  "data": {
    "id": 2,
    "total_amount": "6696.00",
    "total_cost": "4549.00",
    "total_profit": "2147.00",
    "profit_margin": 32.07,
    "status": "completed",
    "items": [
      {
        "product_id": 1,
        "product_name": "Notebook Dell Inspiron 15",
        "quantity": 1,
        "unit_price": "4999.00",
        "subtotal": "4999.00",
        "profit": "1499.00"
      },
      {
        "product_id": 2,
        "product_name": "Mouse Logitech MX Master 3",
        "quantity": 2,
        "unit_price": "549.00",
        "subtotal": "1098.00",
        "profit": "398.00"
      },
      {
        "product_id": 5,
        "product_name": "Headset HyperX Cloud II",
        "quantity": 1,
        "unit_price": "599.00",
        "subtotal": "599.00",
        "profit": "199.00"
      }
    ],
    "created_at": "2024-01-17T19:40:00.000000Z"
  }
}
```

### 5. Consultar Detalhes de uma Venda

```bash
curl -X GET http://localhost:8000/api/v1/sales/1 \
  -H "Accept: application/json"
```

**Resposta esperada:**
```json
{
  "data": {
    "id": 1,
    "total_amount": "9998.00",
    "total_cost": "7000.00",
    "total_profit": "2998.00",
    "profit_margin": 29.99,
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
    "created_at": "2024-01-17T19:35:00.000000Z",
    "updated_at": "2024-01-17T19:35:00.000000Z"
  }
}
```

## 🚫 Exemplos de Erros

### Erro: Produto não encontrado

```bash
curl -X POST http://localhost:8000/api/v1/inventory \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "product_id": 999,
    "quantity": 10
  }'
```

**Resposta (422):**
```json
{
  "message": "The product id field must exist in products.",
  "errors": {
    "product_id": [
      "Product not found"
    ]
  }
}
```

### Erro: Estoque insuficiente

```bash
curl -X POST http://localhost:8000/api/v1/sales \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 1000
      }
    ]
  }'
```

**Resposta (400):**
```json
{
  "message": "Failed to create sale",
  "error": "Insufficient stock for product: Notebook Dell Inspiron 15"
}
```

### Erro: Venda não encontrada

```bash
curl -X GET http://localhost:8000/api/v1/sales/999 \
  -H "Accept: application/json"
```

**Resposta (404):**
```json
{
  "message": "Sale not found"
}
```

### Erro: Validação - Campos obrigatórios

```bash
curl -X POST http://localhost:8000/api/v1/sales \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

**Resposta (422):**
```json
{
  "message": "The items field is required.",
  "errors": {
    "items": [
      "At least one item is required"
    ]
  }
}
```

## 🧪 Cenários de Teste

### Cenário 1: Fluxo Completo de Venda

```bash
# 1. Verificar estoque inicial
curl -X GET http://localhost:8000/api/v1/inventory -H "Accept: application/json"

# 2. Adicionar mais estoque se necessário
curl -X POST http://localhost:8000/api/v1/inventory \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"product_id": 1, "quantity": 10}'

# 3. Criar uma venda
curl -X POST http://localhost:8000/api/v1/sales \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"items": [{"product_id": 1, "quantity": 2}]}'

# 4. Verificar estoque atualizado
curl -X GET http://localhost:8000/api/v1/inventory -H "Accept: application/json"

# 5. Consultar detalhes da venda
curl -X GET http://localhost:8000/api/v1/sales/1 -H "Accept: application/json"
```

### Cenário 2: Venda com Múltiplos Produtos

```bash
curl -X POST http://localhost:8000/api/v1/sales \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 1},
      {"product_id": 2, "quantity": 3},
      {"product_id": 3, "quantity": 2},
      {"product_id": 4, "quantity": 1},
      {"product_id": 5, "quantity": 2}
    ]
  }'
```

### Cenário 3: Reposição de Estoque

```bash
# Adicionar estoque para todos os produtos
for i in {1..5}; do
  curl -X POST http://localhost:8000/api/v1/inventory \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{\"product_id\": $i, \"quantity\": 20}"
  echo ""
done
```

## 📊 Análise de Dados

### Verificar Lucro Projetado Total

```bash
curl -X GET http://localhost:8000/api/v1/inventory \
  -H "Accept: application/json" | jq '.summary.projected_profit'
```

### Listar Produtos com Maior Margem de Lucro

```bash
curl -X GET http://localhost:8000/api/v1/inventory \
  -H "Accept: application/json" | jq '.data | sort_by(.projected_profit) | reverse'
```

### Calcular Valor Total em Estoque

```bash
curl -X GET http://localhost:8000/api/v1/inventory \
  -H "Accept: application/json" | jq '.summary.total_value'
```

## 🔍 Dicas

1. **Use jq para formatar JSON**: Instale `jq` e adicione `| jq` no final dos comandos curl
2. **Salve respostas em arquivos**: Adicione `-o response.json` para salvar a resposta
3. **Verbose mode**: Use `-v` para ver headers e detalhes da requisição
4. **Postman**: Importe `postman_collection.json` para uma interface mais amigável

## 📝 Notas

- Todos os preços estão em formato decimal com 2 casas decimais
- As datas seguem o formato ISO 8601
- O estoque é atualizado automaticamente após cada venda
- As vendas são criadas com status "completed" e disparam eventos de atualização de estoque
