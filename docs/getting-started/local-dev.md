# How to setup a local development environment

## Local development environment with Ubuntu

### Docker

```bash
sudo apt update
sudo apt upgrade
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
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.4-cli php8.4-fpm php8.4-common php8.4-curl php8.4-gd php8.4-iconv php8.4-intl php8.4-ldap php8.4-mbstring php8.4-mysql php8.4-pgsql php8.4-soap php8.4-sqlite3 php8.4-tidy php8.4-xml php8.4-zip php8.4-redis
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --install-dir=/usr/bin --filename=composer
rm composer-setup.php
#Got to your dev workspace
git clone git@github.com:ems-project/elasticms.git
cd elasticms
composer install
wget https://get.symfony.com/cli/installer -O - | bash
sudo mv ~/.symfony5/bin/symfony /usr/local/bin/symfony 
sudo apt install make
```

If you are using a mac with [mac port](https://www.macports.org/):

```bash
sudo port install php84 php84-curl php84-gd php84-iconv php84-intl php84-ldap php84-mbstring php84-mysql php84-soap php84-tidy php84-zip
sudo port select php php84
```

### Switch between multiple PHP versions


Switch between already installed PHP versions:
```bash
sudo update-alternatives --config php
```

Add a specific version:

```bash
export PHP_VERSION=8.1
sudo apt install php${PHP_VERSION} php${PHP_VERSION}-cli php${PHP_VERSION}-fpm php${PHP_VERSION}-common php${PHP_VERSION}-curl php${PHP_VERSION}-gd php${PHP_VERSION}-iconv php${PHP_VERSION}-intl php${PHP_VERSION}-cli php${PHP_VERSION}-fpm php${PHP_VERSION}-ldap php${PHP_VERSION}-mbstring php${PHP_VERSION}-mysql php${PHP_VERSION}-pgsql php${PHP_VERSION}-soap php${PHP_VERSION}-sqlite3 php${PHP_VERSION}-tidy php${PHP_VERSION}-xml php${PHP_VERSION}-zip php${PHP_VERSION}-redis
```




### Instal npm

```bash
sudo apt install -y nodejs npm
```

### Configure git

```bash
echo -e ".idea\nThumbs.db\n.DS_Store\n.vscode\n" > ~/.gitignore
git config --global core.excludesfile ~/.gitignore
git config --global core.autocrlf false
git config --global core.eol lf
```