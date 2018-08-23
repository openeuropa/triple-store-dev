FROM composer:1.7 AS build
WORKDIR /workspace

COPY composer.json composer.json
COPY composer.lock composer.lock
COPY RoboFile.php RoboFile.php
COPY robo.yml robo.yml

RUN composer --no-interaction install

FROM tenforce/virtuoso:1.3.1-virtuoso7.2.2
WORKDIR /workspace

RUN apt-get update && apt-get install -qq -y wget curl php7.0

COPY --from=build /workspace /workspace

RUN ./vendor/bin/robo fetch
