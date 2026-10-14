FROM mcr.microsoft.com/devcontainers/php:8.3

# Xdebug desactivado por defecto (más rápido y sin avisos).
# Para depurar: XDEBUG_MODE=debug php ...
ENV XDEBUG_MODE=off

# Driver PDO para MySQL + cliente de terminal para importar el .sql
RUN docker-php-ext-install pdo_mysql \
 && apt-get update && apt-get install -y default-mysql-client \
 && rm -rf /var/lib/apt/lists/*

# Solo en desarrollo: el cliente no verifica el certificado autofirmado de MySQL
RUN mkdir -p /etc/mysql/conf.d \
 && printf "[client]\nskip-ssl-verify-server-cert\n" > /etc/mysql/conf.d/dev-client.cnf