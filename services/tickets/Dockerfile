FROM php:7.4-cli
RUN docker-php-ext-install pdo pdo_mysql
WORKDIR /app
COPY . /app
CMD ["php", "-S", "0.0.0.0:80", "-t", "/app"]
