# Docker Environment - Laravel API REST

Este projeto está configurado para rodar em containers Docker com PostgreSQL.

## 🐳 Serviços Docker

- **app**: Aplicação Laravel (PHP 8.2-FPM)
- **nginx**: Servidor web (Nginx Alpine)
- **db**: Banco de dados PostgreSQL 16
- **pgadmin**: Interface web para gerenciar PostgreSQL (opcional)

## 📋 Pré-requisitos

- Docker
- Docker Compose

## 🚀 Como Iniciar

### 1. Configurar variáveis de ambiente

Copie o arquivo de ambiente para Docker:

```bash
cp .env.docker .env
```

### 2. Construir e iniciar os containers

```bash
docker compose up -d --build
```

Este comando irá:
- Construir a imagem Docker da aplicação
- Iniciar todos os serviços (app, nginx, db, pgadmin)
- Criar o volume para persistência do PostgreSQL

### 3. Gerar a chave da aplicação

```bash
docker compose exec app php artisan key:generate
```

### 4. Executar as migrations

```bash
docker compose exec app php artisan migrate
```

### 5. Instalar dependências (se necessário)

Se você precisar reinstalar as dependências:

```bash
# Dependências PHP
docker compose exec app composer install

# Dependências Node
docker compose exec app npm install
docker compose exec app npm run build
```

## 🌐 Acessar a Aplicação

- **API**: http://localhost:8000
- **PgAdmin**: http://localhost:5050
  - Email: admin@admin.com
  - Senha: admin

## 🗄️ Configuração do PostgreSQL

### Credenciais padrão:
- **Host**: db (dentro do Docker) ou localhost (fora do Docker)
- **Porta**: 5432
- **Database**: api_estoque
- **Username**: laravel
- **Password**: secret

### Conectar ao PostgreSQL via PgAdmin:

1. Acesse http://localhost:5050
2. Faça login com as credenciais acima
3. Adicione um novo servidor:
   - **Host**: db
   - **Port**: 5432
   - **Database**: api_estoque
   - **Username**: laravel
   - **Password**: secret

## 📝 Comandos Úteis

### Ver logs dos containers

```bash
# Todos os serviços
docker compose logs -f

# Apenas a aplicação
docker compose logs -f app

# Apenas o banco de dados
docker compose logs -f db
```

### Executar comandos Artisan

```bash
docker compose exec app php artisan [comando]
```

Exemplos:
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

### Acessar o container

```bash
# Acessar bash do container da aplicação
docker compose exec app bash

# Acessar PostgreSQL CLI
docker compose exec db psql -U laravel -d api_estoque
```

### Parar os containers

```bash
docker compose down
```

### Parar e remover volumes (CUIDADO: apaga o banco de dados)

```bash
docker compose down -v
```

### Reconstruir os containers

```bash
docker compose up -d --build --force-recreate
```

## 🔧 Ajustar Permissões (se necessário)

Se você encontrar problemas de permissão:

```bash
docker compose exec app chown -R laravel:www-data /var/www
docker compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

## 🧪 Executar Testes

```bash
docker compose exec app php artisan test
```

## 📦 Estrutura de Arquivos Docker

```
.
├── Dockerfile                      # Imagem da aplicação Laravel
├── docker-compose.yml              # Orquestração dos serviços
├── .dockerignore                   # Arquivos ignorados no build
├── .env.docker                     # Variáveis de ambiente para Docker
└── docker/
    ├── nginx/
    │   └── default.conf           # Configuração do Nginx
    └── php/
        └── local.ini              # Configuração customizada do PHP
```

## 🐛 Troubleshooting

### Erro de conexão com o banco de dados

Certifique-se de que:
1. O serviço `db` está rodando: `docker compose ps`
2. As credenciais no `.env` estão corretas
3. O host do banco é `db` (não `localhost` ou `127.0.0.1`)
4. Aguarde o PostgreSQL ficar pronto: `make wait-db`

O Docker Compose agora inclui um healthcheck que garante que o PostgreSQL esteja pronto antes de iniciar a aplicação.

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

## 📚 Recursos Adicionais

- [Documentação do Docker](https://docs.docker.com/)
- [Documentação do Laravel](https://laravel.com/docs)
- [Documentação do PostgreSQL](https://www.postgresql.org/docs/)
