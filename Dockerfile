FROM tenforce/virtuoso:1.3.1-virtuoso7.2.2

RUN apt-get update && apt-get install -qq -y wget curl zip unzip php7.0 php7.0-zip composer

WORKDIR /app

COPY composer.json composer.json
COPY composer.lock composer.lock
COPY RoboFile.php RoboFile.php
COPY robo.yml robo.yml
COPY run.sh run.sh

RUN composer --no-interaction install
RUN ./vendor/bin/robo fetch

CMD ["/bin/bash", "run.sh"]
