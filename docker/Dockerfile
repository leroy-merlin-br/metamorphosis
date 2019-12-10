FROM leroymerlinbr/php:7.2
LABEL maintainer="boitata@leroymerlin.com.br"

USER root:root

RUN cd /tmp \
  && git clone https://github.com/edenhill/librdkafka.git \
  && cd librdkafka \
  && ./configure \
  && make \
  && make install \
  && rm -rf /tmp/*

RUN pecl install rdkafka \
  && docker-php-ext-enable \
    rdkafka \
    xdebug \
  && rm -rf /tmp/*

ARG UID=1000
ARG GID=1000

RUN groupmod -g ${GID} www-data \
  && usermod -u ${UID} -g www-data www-data \
  && chown -hR www-data:www-data \
    /var/www \
    /usr/local/

USER www-data:www-data
