FROM composer:2 AS build
WORKDIR /app

COPY composer.json composer.json
COPY composer.lock composer.lock
COPY RoboFile.php RoboFile.php
COPY robo.yml robo.yml
COPY run.sh run.sh

RUN composer --no-interaction install

FROM openlink/virtuoso-opensource-7:latest

ENV IMPORT_DIR /tmp/import
ENV DBA_PASSWORD dba

RUN apt-get update && apt-get install -qq -y wget curl php7.4-cli php7.4-zip netcat

COPY --from=build /app .

RUN ./vendor/bin/robo fetch

CMD ["/bin/bash", "run.sh"]
