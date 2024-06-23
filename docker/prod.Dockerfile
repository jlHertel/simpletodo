FROM php:8.3-cli AS build

RUN mkdir -p /app/build
COPY ./ /app/build
WORKDIR /app/build

RUN chmod +x docker/install_composer.sh \
    && chmod +x bin/console \
    && docker/install_composer.sh \
    && apt-get update \
    && apt-get install -y zip

RUN php composer.phar install --classmap-authoritative --no-interaction --no-scripts --no-plugins
RUN bin/console cache:warmup --env=prod

# The below command is to populate the database
# Normally this isn't supposed to be here, but we are just playing around :wink:
RUN bin/console doctrine:migrations:migrate -n

# Remove unnecessary files for PROD
RUN rm -rf bin/  \
    tests/ \
    .dockerignore \
    .env.test \
    phpunit.xml.dist \
    composer.phar \
    docker

# Flat layers
FROM php:8.3-cli AS deploy

WORKDIR /app
COPY --from=build /app/build /app

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public/"]