FROM php:8.2-fpm

# Argumentos de build
ARG user=laravel
ARG uid=1000

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd sockets

# Obter última versão do Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário do sistema para executar comandos do Composer e Artisan
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar arquivos de dependências
COPY composer.json package.json package-lock.json ./
COPY composer.lock* ./

# Instalar dependências do PHP
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Instalar dependências do Node
RUN npm ci

# Copiar código da aplicação
COPY . .

# Finalizar instalação do Composer
RUN composer dump-autoload --optimize

# Compilar assets
RUN npm run build

# Ajustar permissões
RUN chown -R $user:www-data /var/www
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

USER $user

EXPOSE 9000

CMD ["php-fpm"]
