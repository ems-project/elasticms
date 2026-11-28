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
sudo apt install php8.5-cli php8.5-fpm php8.5-common php8.5-curl php8.5-gd php8.5-iconv php8.5-intl php8.5-ldap php8.5-mbstring php8.5-mysql php8.5-pgsql php8.5-soap php8.5-sqlite3 php8.5-tidy php8.5-xml php8.5-zip php8.5-redis php8.5-opentelemetry
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

If you are using a mac with [brew](https://brew.sh/):

```bash
brew tap shivammathur/php
brew tap shivammathur/extensions
brew install shivammathur/php/php@8.5
brew install shivammathur/extensions/redis@8.5
brew link --overwrite --force shivammathur/php/php@8.5
pecl install opentelemetry
brew install composer
brew install prettier
brew install symfony-cli/tap/symfony-cli
composer install
brew install make
```

### Switch between multiple PHP versions

Switch between already installed PHP versions:

```bash
sudo update-alternatives --config php
```

Add a specific version:

```bash
export PHP_VERSION=8.4
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
