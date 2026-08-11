# phpIPAM common OS image layer

FROM alpine:3.24

RUN apk upgrade --no-cache \
    && apk add --no-cache tini tzdata alpine-conf mariadb-client \
       php83 php83-cli php83-pear php83-phar php83-pdo_mysql php83-json php83-session php83-gmp php83-gd php83-sockets php83-gettext php83-mbstring \
       php83-ctype php83-ldap php83-curl php83-snmp php83-openssl php83-simplexml php83-pcntl php83-iconv php83-opcache php83-posix php83-dom \
       iputils fping \
       apache2 apache2-ssl php83-apache2 \
       ca-certificates \
    && ln -s /usr/bin/php83 /usr/bin/php

COPY root/ /
