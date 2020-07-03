# phpIPAM common OS image layer

FROM alpine:3.10

RUN apk upgrade --no-cache \
    && apk add --no-cache tini git tzdata alpine-conf mariadb-client \
       php php-cli php-pear php-pdo_mysql php-json php-session php-gmp php-gd php-sockets php-gettext php-mbstring \
       php-ctype php-ldap php-curl php-snmp php-openssl php-simplexml php-pcntl php-iconv php-opcache php-posix php-dom \
       iputils fping \
       apache2 php-apache2 \
    && sed -i 's/#LoadModule rewrite_module modules\/mod_rewrite.so/LoadModule rewrite_module modules\/mod_rewrite.so/' /etc/apache2/httpd.conf

COPY root/ /
