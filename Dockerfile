#syntax=docker/dockerfile:1

LABEL org.opencontainers.image.source=https://github.com/yohang/uuid-benchmark
LABEL org.opencontainers.image.licenses=MIT

ARG FRANKENPHP_VERSION=1.9
ARG PHP_VERSION=8.4
ARG DEBIAN_VERSION=trixie

FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION}-${DEBIAN_VERSION} AS php

LABEL org.opencontainers.image.source=https://github.com/yohang/uuid-benchmark


# persistent / runtime deps
# hadolint ignore=DL3008
RUN apt update && apt install -y --no-install-recommends \
	file \
	gettext \
	git \
    libnss3-tools \
	&& rm -rf /var/lib/apt/lists/*

RUN set -eux; \
	install-php-extensions \
		@composer \
		apcu \
		opcache \
		zip \
		pdo_pgsql \
	;

ARG EXTERNAL_USER_ID

RUN set -eux; \
    sed -i -r s/"(www-data:x:)([[:digit:]]+):([[:digit:]]+):"/\\1${EXTERNAL_USER_ID}:${EXTERNAL_USER_ID}:/g /etc/passwd; \
    sed -i -r s/"(www-data:x:)([[:digit:]]+):"/\\1${EXTERNAL_USER_ID}:/g /etc/group; \
    mkdir -p /var/run/php /app/var /var/www /data /config; \
    chown -R www-data:www-data /usr/local/etc/php /var/run/php /var/www /app /app/var /data /config

USER www-data

VOLUME /config
VOLUME /data
VOLUME /app/var/


ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

###> recipes ###
###< recipes ###

COPY --chown=www-data infra/frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --chown=www-data --chmod=755 infra/frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --chown=www-data infra/frankenphp/Caddyfile /etc/caddy/Caddyfile

COPY --chown=www-data infra/frankenphp/conf.d/20-app.ini-prod $PHP_INI_DIR/app.conf.d/20-app.ini


ENV APP_ENV=prod

WORKDIR /app

# prevent the reinstallation of vendors at every changes in the source code
COPY --chown=www-data composer.* symfony.* ./
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# copy sources
COPY --chown=www-data . ./

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync;


ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1
CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile" ]
