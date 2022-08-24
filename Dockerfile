FROM composer:1.7 AS build
WORKDIR /app

COPY composer.json composer.json
COPY composer.lock composer.lock
COPY RoboFile.php RoboFile.php
COPY robo.yml robo.yml
COPY run.sh run.sh

RUN composer --no-interaction --no-dev install

FROM tenforce/virtuoso:1.3.1-virtuoso7.2.2

ENV IMPORT_DIR /tmp/import
ENV DBA_PASSWORD dba

RUN apt-get update && apt-get install -qq -y wget curl php7.0 php7.0-zip netcat

COPY --from=build /app .

RUN ./vendor/bin/robo fetch

CMD ["/bin/bash", "run.sh"]
