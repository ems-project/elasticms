# Development environment

<!-- TOC -->
* [Development environment](#development-environment)
  * [Start external micro-services](#start-external-micro-services)
    * [Test your config](#test-your-config)
    * [Local ports exposed](#local-ports-exposed)
  * [Prerequisite](#prerequisite-)
  * [Init elasticMS](#init-elasticms)
  * [Load and save DB dumps](#load-and-save-db-dumps)
  * [Identity provider (IDP) (Keycloak)](#identity-provider-idp-keycloak)
  * [About PHP configuration](#about-php-configuration)
<!-- TOC -->

## Start external micro-services

elasticMS comes with multiple micro-services:

* Redis: cache and session
* elasticsearch: for indexing contents
* Tika: for extracting data from assets
* PosgresSQL or MySQL: As authentic source of data

In order to simplify development all those services are available in a docker compose:

```bash
make init
make start
```

### Test your config

* [Traefik](http://localhost:8888/dashboard/#/): The middleware application used to route packages
* [Kibana](http://kibana.localhost/app/dev_tools#/console): Power tools for elasticsearch
* [es01](http://es01.localhost/),[es02](http://es02.localhost/),[es03](http://es03.localhost/): the hearts of elasticMS
* [Mailhog](http://mailhog.localhost/): A mail catcher
* [MinIO](http://minio.localhost/login): A S3 like service
* [Tika](http://tika.localhost): A S3 like service
* [Redis Commander](http://redis-commander.localhost): A Redis inspector tool


### Local ports exposed

| Port | service         |
|------|-----------------|
| 80   | traefik         |
| 442  | traefik TLS     |
| 1025 | mailhog         |
| 3306 | mariadb         |
| 5432 | postgres        |
| 5601 | kibana          |
| 6379 | redis           |
| 8888 | traefik's admin |
| 9000 | minio           |
| 9998 | tika            |

## Prerequisite 

```bash
cd ~
wget https://get.symfony.com/cli/installer -O - | bash
sudo mv ~/.symfony5/bin/symfony /usr/local/bin/symfony 
```

## Init elasticMS

````bash
make init 
make start
make db-create/"db_example" SCHEMA="schema_example_adm"
cd elasticms-admin
# change DB_URL in .env.local -> DB_URL="pgsql://db_example:db_example@127.0.0.1:5432/db_example"
php bin/console doctrine:migrations:migrate
php bin/console emsco:user:create --super-admin
php bin/console asset:install --symlink
````

[elasticMS Admin](http://localhost:8881) is now available.

Useful make commands:

* `make server-log/admin`
* `make server-log/web`

## Load and save DB dumps

You may want to load an existing elasticMS dump. If so please check the dump's schema matches the DB's schema.

```bash
make db-load/"db_example" DUMP=./dump_example.sql
```

To make a dump:

```bash
make db-dump/"db_example" SCHEMA="schema_example_adm"
```

## Admin UI

The Admin UI bundles uses vite for building the assets, while the core bundle uses webpack.
Vite comes with a great dev server, which will automatic reload pages on js/css and twig changes.

1) Installation
```bash
cd EMS/admin-ui-bundle
npm install
npm run build
```

2) Running dev server
```bash
npm run dev
npm run dev -- --debug # debug mode
```

> Make sure you set **EMSUI_DEV_SERVER_URL**='http://localhost:5173'

## Identity provider (IDP) (Keycloak)

Elasticms-web has a build in OAuth2 and SAML authenticator. see [elasticms-web/security](/elasticms-web/security.md).

For developing and testing purposes you may want to start an IDP. 
Therefor we created a subdirectory 'idp' containing the services (keycloak & postgres).

```bash
cd docker/idp 
docker compose up -d
```

1) Check if available on http://keycloak.localhost or http://localhost:9081

   Administration Console -> `admin:changeme`

2) Import the data and restart the keycloak service
    ```bash
    docker compose exec keycloak sh /opt/keycloak/bin/kc.sh import --dir /data
    docker compose up -d --force-recreate
    ```

    If you want to export the data and versioning new settings
    ```bash
    docker compose exec keycloak sh /opt/keycloak/bin/kc.sh export --dir /data --users same_file --realm elasticms
    ```
   
3) Verify `elasticms` realm is created

   Visit http://keycloak.localhost/realms/elasticms

4) Login with elasticms users

   http://keycloak.localhost/realms/elasticms/account

   - user1@example.com changeme
   - user2@example.com changeme

5) Use the following environment variables in `elasticms-web`

```.dotenv
EMSCH_OAUTH2=true
EMSCH_OAUTH2_AUTH_SERVER='http://keycloak.localhost'
EMSCH_OAUTH2_REALM='elasticms'
EMSCH_OAUTH2_CLIENT_ID='demo-skeleton-oauth2'
EMSCH_OAUTH2_CLIENT_SECRET='zuB3n0uZ4Ioo27ugsIjiT7vFVqmz5Zfq'
EMSCH_OAUTH2_REDIRECT_URI='http://localhost:8882/oauth2/connect'
EMSCH_OAUTH2_VERSION=24.0.3
EMSCH_OAUTH2_ENCRYPTION_ALGORITHM=RS256
EMSCH_OAUTH2_ENCRYPTION_KEY=LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSUNvVENDQVlrQ0JnR0dPc2hTZ0RBTkJna3Foa2lHOXcwQkFRc0ZBREFVTVJJd0VBWURWUVFEREFsbGJHRnoKZEdsamJYTXdIaGNOTWpNd01qRXdNVEF3TWpNeVdoY05Nek13TWpFd01UQXdOREV5V2pBVU1SSXdFQVlEVlFRRApEQWxsYkdGemRHbGpiWE13Z2dFaU1BMEdDU3FHU0liM0RRRUJBUVVBQTRJQkR3QXdnZ0VLQW9JQkFRRElDY3lEClA0S3doUHVJV0xza0JJdDRjanFLdGV3K05wZTNSKzJ0SEc5QkMrYjZHMGNSNzhrWThTZ3pmTkNCTC9RZTVHK0kKTUE4YU44cktienJQWEJvd1JNS0hoRlhqRy8rR042M2docFNVeFN2ZHpMcEc5UGFVaExEZTVXR21wTFMycTcrdQp2QjRJZGtiYytJaENqV0VHMEJQSThCaXpkZVJEYWJ2NmVVYWFHVzYyT0VwU0tXQWxQSzZ4K0x0YXZSK0xCaXhnCm5ob0FrNjZGYXZjdUxYS28yR2x1djB6UVB5elhMRzdhMytyaXJmMjRXRndhMElHemxSd0xmTHpnRXFYQ0dIVjcKaDJ1VmRFNDVRc0Nmb0pZazJsWmRvOHJlSFIxK3VSdkNjM3pIOGVHdUY1eVpIS09neWMvZTRHYXNZdEJ1Vm5qaQoxQmJRaEJQSi84RlhBTWtSQWdNQkFBRXdEUVlKS29aSWh2Y05BUUVMQlFBRGdnRUJBSHc5WGZLTHhCcHZZbGNHCnJFUVNCVmg2dTY0cnRSZ3pNRkpaNlV6S3FQWG16a0s1WDlUVVBFYVFvbnBQc0lldFFySWtaOWVzMlYvd1Q5RHgKUmJ0MnVUTTE1aksvZmxLclBoTWdwdnArWHBpTUVOMEtVdFVwakptRjRxYVJrY0ZpbjYyL2FicHhtaW1pNEptNAp2UGE0VmJ0cVY4YWp3aGl5T1IyOUVwRWd1WHBxLzVDUWVucWhQR09XSlpFSW1nK3JEcjlDNHVQWDFtdFBKRTZsCmZQQ08wSkZkRnpwbzlFdU90VW5XbVVRVTMrUHZzSnZSWk9Xd0pGMWhEeTVsdTBKRnh1dVRDeXNXQythQ3o5NDEKU1cvSGs4a0M2OUxYNXUwcEcvSWZlNE1XRG11cnRacHI0Q21Wc3NHUEl3eC9zRm5oOFNwck1KZ21LTE1EdkdqeQpTdnB0TjRJPQotLS0tLUVORCBDRVJUSUZJQ0FURS0tLS0t

EMSCH_SAML=true
EMSCH_SAML_SP_ENTITY_ID='demo-skeleton-saml'
EMSCH_SAML_SP_PUBLIC_KEY='MIIC2zCCAcMCBgGGPHWIuzANBgkqhkiG9w0BAQsFADAxMS8wLQYDVQQDDCZodHRwOi8vbG9jYWxob3N0Ojg4ODIvc2FtbC9sb2dpbl9jaGVjazAeFw0yMzAyMTAxNzUxMjFaFw0zMzAyMTAxNzUzMDFaMDExLzAtBgNVBAMMJmh0dHA6Ly9sb2NhbGhvc3Q6ODg4Mi9zYW1sL2xvZ2luX2NoZWNrMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0xU6ggTVggU3aG79Iz28R30IaRk47/LUxOEoBKbwr+Y+krFhlDfkJXnAxX8vIkeXBoYFVZ9BV+ZpzFv2kqdHbPWrqdti/q0DIRuczczO+stHGuJjrohkj6YznwGj5wNyIpeTid0G6uud2Ke72MAIo1whcm4zQ1Sw7Pl8MHp19nfFvOwri+COW/iJrrDn5PCn7/4QqquLgaUd/PXNt3jDOO7S0llY4Ra38KxCmLvFkQ63maTNO74HZTVBwZNB9W7YoNa82EL4jMafS+rB68jkfF8+8YUh8ZrcmDpyREzVNZ8n76AGWlhQBXLiWg8I/xEMKtBvW069IePwKqxbcXxbyQIDAQABMA0GCSqGSIb3DQEBCwUAA4IBAQDJccjFJTxgVRlR2DweY172gNLiMXEhhEfxHGjLU7dYvLpc4euiuR0MGfexm4eSDhfy0LsEr2LAr8fu5SqHjgGTIm6ttNFNL2WuWEZkMs0cUqc2uY4RiQyitvgpS6A65ggYFohxpdIZIvUcFtENiLnfLiGfUE3Z+ZYT5NuZbnzbP2WMdL1XmFE732UzTQlOii4aCS54IbWR1ZLdhkzaIb5eQJfGA97XYZT+OnDYKkFPZb3MQFwXNFOOOdpJ17cyUqJtzqz/GQaozacxdAy2GRZumoUWphvNzSuDMUQLdjnCt8jPkNz38NUjypRla/rotsAY7dzpbMMHqhyj8wXA3yeT'
EMSCH_SAML_SP_PRIVATE_KEY='MIIEowIBAAKCAQEA0xU6ggTVggU3aG79Iz28R30IaRk47/LUxOEoBKbwr+Y+krFhlDfkJXnAxX8vIkeXBoYFVZ9BV+ZpzFv2kqdHbPWrqdti/q0DIRuczczO+stHGuJjrohkj6YznwGj5wNyIpeTid0G6uud2Ke72MAIo1whcm4zQ1Sw7Pl8MHp19nfFvOwri+COW/iJrrDn5PCn7/4QqquLgaUd/PXNt3jDOO7S0llY4Ra38KxCmLvFkQ63maTNO74HZTVBwZNB9W7YoNa82EL4jMafS+rB68jkfF8+8YUh8ZrcmDpyREzVNZ8n76AGWlhQBXLiWg8I/xEMKtBvW069IePwKqxbcXxbyQIDAQABAoIBAAWIiu4Znlc4N8mfDze7SJI/LtdCeAGiRf2bQWdN0QVrbbx+teYiyPJKjMkgmmW1prnfDYi/EgFx4tgemQojJHYwwn1DaQbwFiLqDGRAuDRO2+BSivZqUOiLHNNQQbGun3FUs+NrTeEeqBaj3wLBlfhiU+YiCWn8cF7l82F59FmvoecdYhGo0Gs4tKMl58iiBfw1qelDrYnbXDLQ3gvOCH0EIA7+zv+rjT5oWvomMuPxq9rHRt01voyopd4Oykb7R7DERCrjXotoywYmU0TCWGAOPoWe0n0W8uE7aAqVl3RBiKeS3xVn4mDhetvnt3drLrvi+VoQ5NrFXhnSiGhnDAECgYEA8WbU2GvBCTbPk2XWqHtRoU6/43JNKRwc9DBlIxPcHTRXWZMkHV6TPFpLMwklhkU+FzLp87pdvkVeXY1lzMRgXS/kBxEp3QpFuJa8pycBN34tY6t7NZbd4APkROAUAHxzmFjlqKKXC9mm9o6TGToWxbIYMlHbXars8py64CmVZ4kCgYEA39kHWmuwWLwWzcaJWF26plWZDhZ0ncWoo6nV+hxMb+t3bNxvDKYxsFfavrjWKrYf7h4kZv4n/ttqCQ2ih7P0J/0GPYDVio45nA/gHBjhIvMTX1Epy5XUiOudNu32XdXuVLav1l/+GD98r6+usZyl6aCysIOaRsdluW2pn8xOAkECgYEAnbL8puk1sMxCrFrh8SymdgdY6b/Y0ltQOuGGoHSv3dA8pAKwnBMVgl7GM2/tOJrxZfxXw2XjKZJthBYEA9Hh1d6cS1WWEJVOWLj4QwYDEHp4Ml1q3uZQybAhJjFwP6UNat/gH9sfa5ljLjyTse41xC6FChJZiQZJRnDGz/u6Y2kCgYApCdHmSt3utrT7js15TN2+Ru0jfwxsLGOpdaaMDwoYbrPbWmJlkEaFzOWGl75z4CXkctQ7qZbNi45aEIzekihN+H5fYjJED6USLnroy8rirGu9ytR9xX9Mht2wx1mmhGUIVOHRzJF6ApGqZ+wAFfb46QQ5hjcPiNjmcOtrJ3qZwQKBgFguJ8dyjKWSooUhXZyOywjbP9uqsl/cJi9GgkaVQ7cVLwgB9D517PO+GvIuc1nmQ3kyaHpv79PxaRcY6+VX0e8giAS1yMH+Av6j47rshTmS3GP6MGEsrsOHZTLjG+MFfIyUOy0zWPE+VPj+vRhIWZo+EdfhAWXjLt+C22RvCTMd'
EMSCH_SAML_IDP_ENTITY_ID='http://keycloak.localhost/realms/elasticms'
EMSCH_SAML_IDP_PUBLIC_KEY='MIICoTCCAYkCBgGGOshSgDANBgkqhkiG9w0BAQsFADAUMRIwEAYDVQQDDAllbGFzdGljbXMwHhcNMjMwMjEwMTAwMjMyWhcNMzMwMjEwMTAwNDEyWjAUMRIwEAYDVQQDDAllbGFzdGljbXMwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDICcyDP4KwhPuIWLskBIt4cjqKtew+Npe3R+2tHG9BC+b6G0cR78kY8SgzfNCBL/Qe5G+IMA8aN8rKbzrPXBowRMKHhFXjG/+GN63ghpSUxSvdzLpG9PaUhLDe5WGmpLS2q7+uvB4Idkbc+IhCjWEG0BPI8BizdeRDabv6eUaaGW62OEpSKWAlPK6x+LtavR+LBixgnhoAk66FavcuLXKo2Gluv0zQPyzXLG7a3+rirf24WFwa0IGzlRwLfLzgEqXCGHV7h2uVdE45QsCfoJYk2lZdo8reHR1+uRvCc3zH8eGuF5yZHKOgyc/e4GasYtBuVnji1BbQhBPJ/8FXAMkRAgMBAAEwDQYJKoZIhvcNAQELBQADggEBAHw9XfKLxBpvYlcGrEQSBVh6u64rtRgzMFJZ6UzKqPXmzkK5X9TUPEaQonpPsIetQrIkZ9es2V/wT9DxRbt2uTM15jK/flKrPhMgpvp+XpiMEN0KUtUpjJmF4qaRkcFin62/abpxmimi4Jm4vPa4VbtqV8ajwhiyOR29EpEguXpq/5CQenqhPGOWJZEImg+rDr9C4uPX1mtPJE6lfPCO0JFdFzpo9EuOtUnWmUQU3+PvsJvRZOWwJF1hDy5lu0JFxuuTCysWC+aCz941SW/Hk8kC69LX5u0pG/Ife4MWDmurtZpr4CmVssGPIwx/sFnh8SprMJgmKLMDvGjySvptN4I='
EMSCH_SAML_IDP_SSO='http://keycloak.localhost/realms/elasticms/protocol/saml'
```

> *IMPORT* For using xDebug change http://keycloak.localhost -> http://localhost:9081

| Variable                          | Location                                                                                                                                           |
|-----------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------|
| EMSCH_OAUTH2_CLIENT_ID            | [client settings](http://keycloak.localhost/admin/master/console/#/elasticms/clients/55e433be-33a4-46cf-b972-36eccc5cebb0/settings)                |
| EMSCH_OAUTH2_CLIENT_SECRET        | [client credentials](http://keycloak.localhost/admin/master/console/#/elasticms/clients/55e433be-33a4-46cf-b972-36eccc5cebb0/credentials)          |
| EMSCH_OAUTH2_REDIRECT_URI         | Valid redirect URIs on the client settings page                                                                                                    |
| EMSCH_OAUTH2_VERSION              | [master realm info](http://keycloak.localhost/admin/master/console/#/master/info)                                                                  |
| EMSCH_OAUTH2_ENCRYPTION_ALGORITHM | RS256 [client advanced settings](http://keycloak.localhost/admin/master/console/#/elasticms/clients/55e433be-33a4-46cf-b972-36eccc5cebb0/advanced) |
| EMSCH_OAUTH2_ENCRYPTION_KEY       | see below                                                                                                                                          |
|                                   |                                                                                                                                                    |
| EMSCH_SAML_SP_ENTITY_ID           | [client settings](http://keycloak.localhost/admin/master/console/#/elasticms/clients/a959232e-2993-42d2-ab19-0de899880c1a/settings)                |
| EMSCH_SAML_SP_PUBLIC_KEY          | [client keys](http://keycloak.localhost/admin/master/console/#/elasticms/clients/a959232e-2993-42d2-ab19-0de899880c1a/keys)                        |
| EMSCH_SAML_SP_PRIVATE_KEY         | You receive the private key on generation                                                                                                          |
| EMSCH_SAML_IDP_PUBLIC_KEY         | http://keycloak.localhost/realms/elasticms/protocol/saml/descriptor                                                                                |

* Generate oAuth2 encryption key

  This is the private key, received on creation and not stored in keycloak db.
  ```bash
  openssl pkcs12 --nokeys --info -in keystore.p12 
  ```
  EMSCH_OAUTH2_ENCRYPTION_KEY=Base64(-----BEGIN CERTIFICATE-----.....-----END CERTIFICATE-----).

## About PHP configuration

Activate those PHP options inside you php.ini:

```ini
zend.assertions = 1
```

You can easily locate your php.ini file with the command: `php --info|grep php.ini`
