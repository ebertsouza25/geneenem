# GeneEnem — imagem para publicação no Render (PHP 8.3 + Apache).
#
# O projeto é copiado inteiro para /var/www/html, sem mudar a estrutura:
# index.php continua na raiz e as pastas css/, js/, img/ e ia/ seguem iguais.
#
# Extensões instaladas, conforme o código realmente usa:
#   mysqli  -> config.php (conexão MySQL) e Aluno.php
#   pdo_mysql -> disponível para consultas PDO
#   curl    -> ia/cliente.php (chamada da GeneIA)
#   mbstring -> funcoes.php / ia/escopo.php (textos com acento)
#   openssl -> TLS da conexão MySQL e HTTPS da API

FROM php:8.3-apache

RUN docker-php-ext-install mysqli pdo_mysql \
    && a2enmod rewrite headers

# index.php como página inicial (o Apache vem com index.html na frente).
RUN sed -ri 's/^DirectoryIndex .*/DirectoryIndex index.php index.html/' /etc/apache2/mods-enabled/dir.conf

# Permite que os .htaccess do projeto valham (ia/.htaccess protege a chave).
RUN sed -ri '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Erros vão para o log do Render; nada de detalhes técnicos na página.
RUN { \
        echo 'display_errors=Off'; \
        echo 'display_startup_errors=Off'; \
        echo 'log_errors=On'; \
        echo 'error_log=/proc/self/fd/2'; \
        echo 'expose_php=Off'; \
        echo 'session.cookie_httponly=1'; \
        echo 'session.use_strict_mode=1'; \
        echo 'upload_max_filesize=8M'; \
        echo 'post_max_size=8M'; \
    } > /usr/local/etc/php/conf.d/geneenem.ini

# Arquivos que nunca devem ser servidos pelo navegador.
RUN printf '%s\n' \
    '<FilesMatch "^(config\.local\.php|\.env(\..*)?|banco_geneenem\.sql|Dockerfile|\.dockerignore|\.gitignore)$">' \
    '    Require all denied' \
    '</FilesMatch>' \
    > /etc/apache2/conf-available/geneenem-protecao.conf \
    && a2enconf geneenem-protecao

WORKDIR /var/www/html
COPY . /var/www/html/

# O Render injeta a porta em PORT (10000 por padrão). O Apache passa a escutar
# exatamente nessa porta, em 0.0.0.0, e o comando não termina (mantém o serviço).
# Para rodar a imagem no seu computador: docker run -e PORT=8080 -p 8080:8080
EXPOSE 10000
CMD ["sh", "-c", "PORT=\"${PORT:-10000}\"; sed -ri \"s/^Listen .*/Listen ${PORT}/\" /etc/apache2/ports.conf; sed -ri \"s/<VirtualHost \\*:[0-9]+>/<VirtualHost *:${PORT}>/\" /etc/apache2/sites-enabled/000-default.conf; exec apache2-foreground"]
