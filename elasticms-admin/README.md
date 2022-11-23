# ElasticMS [![Build Status](https://github.com/ems-project/elasticms-docker/actions/workflows/ci.yml/badge.svg)](https://github.com/ems-project/elasticms-docker) [![Latest Stable Version](https://img.shields.io/github/release/ems-project/elasticms.svg)](https://github.com/ems-project/elasticms/releases)

## About

A minimal CMS to manage generic content in order to publish it in several Elasticsearch index (based on Symfony 4, Bootstrap 3, and AdminLTE).


## Setup
### Requirements
-   composer
-   an elasticsearch cluster (at least 2 nodes is recommended)
-   mysql or PostrgreSQL
-   optionally an Apache Tika sever

### Installation
Navigate to the root of the project `elasticms` and execute the following command:
> composer install

At the end you will get a list of questions to configure user database and database user. While the user should exist in your mysql environment, you can automatically create the database and schema with the following commands:
```
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
php bin/console emsco:user:create demo demo@example.com mypassword --super-admin
php bin/console ems:make:filter --all
php bin/console ems:make:analyzer --all
php bin/console ems:environment:create preview
php bin/console ems:environment:create live
php bin/console ems:environment:create template
php bin/console ems:make:contenttype page menu --environment=preview
php bin/console ems:make:contenttype label template route asset --environment=template
php bin/console ems:environment:rebuild preview
php bin/console ems:environment:rebuild live
php bin/console ems:environment:rebuild template
php bin/console ems:contenttype:activate --all
php bin/console ems:delete:orphans
php bin/console ems:make:document page vendor/elasticms/maker-bundle/Resources/make/document/page
php bin/console ems:make:document menu vendor/elasticms/maker-bundle/Resources/make/document/menu
php bin/console ems:make:document label vendor/elasticms/maker-bundle/Resources/make/document/label
php bin/console ems:make:document template vendor/elasticms/maker-bundle/Resources/make/document/template
php bin/console ems:env:align template preview  --searchQuery='{"query":{"bool":{"must":[{"terms":{"_contenttype":["template","label","route"]}}]}}}' --force
php bin/console ems:env:align preview template  --searchQuery='{"query":{"bool":{"must_not":[{"terms":{"_contenttype":["template","label","route"]}}]}}}' --force
php bin/console ems:env:align preview live  --force
```


> php bin/console doctrine:database:create
> php bin/console doctrine:migrations:migrate

Verify the project's elasticsearch configuration in `src\AppBundle\Resources\config\parameters.yml`.
And now we can launch Symfony's build in server:
> php bin/console server:run

Then you have to create a super-admin user:
> php bin/console emsco:user:create admin --super-admin


//Todo add information about the elasticsearch cluster

And voila, ElasticMS is up and running!


## SOAP connection
For the soapRequest twig function to work, the following line should be activated in your php.ini
> extension=php_soap.dll

## Database updates (schema and content)
Whenever a new version is released, the database might change. This can be done automatically with migrations.
You can easily get a status of the migrations in relation to your database scheme with the following command:
> php bin/console doctrine:migrations:status

To execute all migrations:
> php bin/console doctrine:migrations:migrate

In the case your database is already set up you should not try to run the initial migration, add it to the "already run" list with the following command:
> php bin/console doctrine:migrations:version --add 20160528181644

So that it does not get executed.
The second migration in this project is safe to run multiple times as it changes data in our database, and not the table schema.
It is adviced to always use migrations for changes so that:
-   we can easily build a DB from scratch and get future changes (use doctrine:migrations:migrate in stead of doctrine:schema:create)
-   everyone can update to a newer version without dataloss (auto generate migrations with doctrine:migrations:diff for schema updates && write migrations for content changes)

```
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
php bin/console emco:user:create demo demo@example.com mypassword --super-admin
php bin/console ems:environment:create preview
php bin/console ems:environment:create live
php bin/console ems:environment:create template
php bin/console ems:make:contenttype menu --environment=preview
php bin/console ems:make:contenttype label template route asset --environment=template
php bin/console ems:environment:rebuild preview
php bin/console ems:environment:rebuild live
php bin/console ems:environment:rebuild template
php bin/console ems:contenttype:activate --all
php bin/console ems:delete:orphans
php bin/console ems:make:document template vendor/elasticms/maker-bundle/Resources/make/document/template.zip
php bin/console ems:make:document label vendor/elasticms/maker-bundle/Resources/make/document/label.zip
php bin/console ems:env:align template preview  --searchQuery='{"query":{"bool":{"must":[{"terms":{"_contenttype":["template","label","route"]}}]}}}' --force
php bin/console ems:env:align preview template  --searchQuery='{"query":{"bool":{"must_not":[{"terms":{"_contenttype":["template","label","route"]}}]}}}' --force
php bin/console ems:env:align preview live  --force
```

## Update dependencies

In order to update composer dependencies (and the elaticms's bundles) run the following command:

In Windows Command Line:
```
docker run -it -v %cd%:/opt/src -w /opt/src docker.io/elasticms/base-php-dev:7.4 composer --no-scripts update 
```
On Linux or in PowerShell
```
docker run -it -v ${PWD}:/opt/src -w /opt/src docker.io/elasticms/base-php-dev:7.4 composer --no-scripts update
```

If you want to udpate elasticms's bundles only:

In Windows Command Line:
```
docker run -it -v %cd%:/opt/src -w /opt/src docker.io/elasticms/base-php-dev:7.4 composer update --no-scripts elasticms/* 
```
On Linux or in PowerShell
```
docker run -it -v ${PWD}:/opt/src -w /opt/src docker.io/elasticms/base-php-dev:7.4 composer update --no-scripts elasticms/*
```