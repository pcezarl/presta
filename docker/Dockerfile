# Espelha o servidor original: XAMPP com PHP 5.3 e MySQL antigo.
# Usamos PHP 5.6 porque e a imagem oficial mais antiga ainda publicada e
# mantem tudo que o codigo precisa: ext/mysql, ereg, split, each, __autoload.
# Nenhuma dessas coisas existe no PHP 7+.
FROM --platform=linux/amd64 php:5.6-apache

RUN docker-php-ext-install mysql mysqli mbstring \
 && a2enmod rewrite

# O PHP 5.6 emite warning em toda chamada de data() sem timezone definido.
# short_open_tag PRECISA ficar Off: 12 arquivos do projeto comecam a saida com
# <?xml version="1.0" ...?>, que o PHP interpreta como abertura de tag quando a
# diretiva esta ligada, resultando em "syntax error, unexpected 'version'".
# Nenhum arquivo do projeto usa short tag de verdade.
RUN printf 'date.timezone = America/Sao_Paulo\n\
display_errors = On\n\
error_reporting = E_ALL & ~E_DEPRECATED & ~E_NOTICE\n\
short_open_tag = Off\n' > /usr/local/etc/php/conf.d/presta.ini

WORKDIR /var/www/html
