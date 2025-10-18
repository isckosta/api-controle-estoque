#!/bin/bash

echo "🚀 Setting up API de Controle de Estoque e Vendas..."
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker and try again."
    exit 1
fi

echo "📦 Step 1: Copying environment file..."
cp .env.docker .env

echo "🐳 Step 2: Building and starting Docker containers..."
docker compose up -d --build

echo "⏳ Step 3: Waiting for PostgreSQL to be ready..."
sleep 10

echo "🔑 Step 4: Generating application key..."
docker compose exec app php artisan key:generate

echo "📊 Step 5: Running migrations..."
docker compose exec app php artisan migrate

echo "🌱 Step 6: Seeding database with test data..."
docker compose exec app php artisan db:seed

echo "📚 Step 7: Installing Swagger dependencies..."
docker compose exec app composer require darkaonline/l5-swagger

echo "📖 Step 8: Publishing Swagger configuration..."
docker compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

echo "📝 Step 9: Generating API documentation..."
docker compose exec app php artisan l5-swagger:generate

echo ""
echo "✅ Setup completed successfully!"
echo ""
echo "🌐 API is available at: http://localhost:8000/api/v1"
echo "📖 Swagger documentation: http://localhost:8000/api/documentation"
echo "🗄️  PgAdmin: http://localhost:5050 (admin@admin.com / admin)"
echo ""
echo "📋 Available endpoints:"
echo "  - POST   /api/v1/inventory"
echo "  - GET    /api/v1/inventory"
echo "  - POST   /api/v1/sales"
echo "  - GET    /api/v1/sales/{id}"
echo ""
echo "🧪 Run tests with: make test"
echo "📚 Import postman_collection.json into Postman to test the API"
echo ""
