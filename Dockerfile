FROM tenforce/virtuoso:1.3.1-virtuoso7.2.2

RUN apt-get update && apt-get install -qq -y wget curl php composer

COPY composer.json /composer.json
COPY RoboFile.php /RoboFile.php
COPY vendor /vendor
COPY robo.yml /robo.yml
COPY run.sh /run.sh

RUN composer --working-dir=/ --no-interaction --quiet install
RUN cd / && /vendor/bin/robo fetch

CMD ["/bin/bash", "/run.sh"]
