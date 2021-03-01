FROM debian:stretch

RUN apt-get update
RUN apt-get -y install gnupg curl wget

# For installing php7
RUN apt -y install lsb-release apt-transport-https ca-certificates
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php7.2.list

RUN apt-get update

RUN apt-get -y install rake php7.2 php7.2-cli php7.2-curl php-pear phpunit php7.2-xml php7.2-mbstring
RUN update-alternatives --set php /usr/bin/php7.2
WORKDIR /braintree-php
