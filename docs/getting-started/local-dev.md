# How to setup a local development environment

## Local development environment with Ubuntu

### Docker

```bash
sudo apt install docker
sudo groupadd docker
sudo usermod -aG docker $USER
newgrp docker
sudo chown root:docker /var/run/docker.sock
```

Then restart your computer.


### Composer and PHP

Composer is required to resolve PHP dependencies.

```bash
sudo apt install composer
sudo apt install php-common php-curl php-gd php-iconv php-intl php-json php-ldap php-mbstring php-mysql php-pgsql php-soap php-sqlite3 php-tidy php-xml php-zip
```

If you are using a mac with [mac port](https://www.macports.org/):

```bash
sudo port install php81 php81-curl php81-gd php81-iconv php81-intl php81-ldap php81-mbstring php81-mysql php81-soap php81-tidy php81-zip
sudo port select php php81
```

### Switch between multiple PHP versions


Switch between already installed PHP versions:
```bash
sudo update-alternatives --config php
```

Add a specific version:

```bash
export PHP_VERSION=8.1
sudo apt install php${PHP_VERSION} php${PHP_VERSION}-common php${PHP_VERSION}-curl php${PHP_VERSION}-gd php${PHP_VERSION}-iconv php${PHP_VERSION}-intl php${PHP_VERSION}-cli php${PHP_VERSION}-fpm php${PHP_VERSION}-ldap php${PHP_VERSION}-mbstring php${PHP_VERSION}-mysql php${PHP_VERSION}-pgsql php${PHP_VERSION}-soap php${PHP_VERSION}-sqlite3 php${PHP_VERSION}-tidy php${PHP_VERSION}-xml php${PHP_VERSION}-zip
```




### Instal npm

```bash
curl -fsSL https://deb.nodesource.com/setup_19.x | sudo -E bash - &&\
sudo apt-get install -y nodejs
```

### Configure git

```bash

echo -e ".idea\nThumbs.db\n.DS_Store\n.vscode\n" > ~/.gitignore
git config --global core.excludesfile ~/.gitignore
git config --global core.autocrlf false
git config --global core.eol lf
```