FROM laravelphp/vapor:php80

ARG IMAGICK_LAST_COMMIT='448c1cd0d58ba2838b9b6dff71c9b7e70a401b90'

RUN mkdir -p /usr/src/php/ext/imagick && \
    curl -fsSL https://github.com/Imagick/imagick/archive/${IMAGICK_LAST_COMMIT}.tar.gz | tar xvz -C /usr/src/php/ext/imagick --strip 1 && \
    docker-php-ext-install imagick

RUN pecl install -D 'enable-openssl="yes" enable-http2="yes"' swoole && docker-php-ext-enable swoole

COPY . /var/task
