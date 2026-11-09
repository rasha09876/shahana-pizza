FROM php:8.2-apache

RUN a2dismod -f mpm_event mpm_worker && a2enmod mpm_prefork

COPY . /var/www/html/

EXPOSE 80
