FROM php:7.4-cli

RUN apt-get update && apt-get install -y git zip unzip

WORKDIR /app/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


EXPOSE 4000

ENTRYPOINT ["php","-S","0.0.0.0:4000","/app/public/index.php"]