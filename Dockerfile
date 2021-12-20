FROM debian:buster

RUN apt-get update
RUN apt-get -y install gnupg curl wget

RUN apt -y install lsb-release apt-transport-https ca-certificates
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/sury-php.list

RUN apt-get update

RUN apt-get -y install rake php8.1 php8.1-cli php8.1-curl php-pear php8.1-xml php8.1-mbstring

RUN update-alternatives --set php /usr/bin/php8.1 && php -v
WORKDIR /braintree-php
