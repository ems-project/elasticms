# elasticms-demo
A default elasticms local setup using docker compose

Resources
---------

* [Documentation](https://ems-project.github.io/#/elasticms-cli/index)
* [Report issues](https://github.com/ems-project/elasticms/issues) and
  [send Pull Requests](https://github.com/ems-project/elasticms/pulls)
  in the [elasticMS mono repository](https://github.com/ems-project/elasticms)

## Prerequisites

You need docker compose (or an alternative as Podman) on a running Linux system (or WSL if you are under Windows).

It's recommended to allow at least 6GB of memory to docker.

The following ports must be available:
 * 8888: Traefik UI
 * 80: Web HTTP
 * 443: Web HTTPS

If your linux user id is different thant 1000, please define a UID variable with your user id:

`export UID=1001`

## Install steps

Open a terminal and run the following commands:
* `sh npm.sh install`: install NPM dependencies
* `sh npm.sh run prod`: Build the frontend assets (js, css, ...)
* `docker compose pull`: Ensure to get the last images
* `docker compose up -d`: Start the docker container (in daemon mode)


### ELK stack version

If you want to use elasticsearch 8 instead of elasticsearch 7, define this variable first:
```bash
export ELK_VERSION=elk8
```

Available stacks:

 * `elk7`: elasticsearch 7.17.7
 * `elk8`: elasticsearch 8.5.3
 * `os2`: OpenSearch 2.4.1




Before continuing, check that all services have been correctly started by running `docker compose ps`. All services must be in `running` status or in `running (healthy)` status.
Except for the `setup_elk` and the `setup_minio` which should be in `exited (0) status.

Go to [minio](http://minio.localhost/login) and login with those credentials:

* user: accesskey
* password: secretkey

Verify that a `demo` bucket has been created, otherwise create it.

Go back to your console:
 * `sh ems.sh create_users local`
   * A `demo` admin user is created with the email and the password that you provide
   * You have now access to an empty [elasticms-admin](http://local.ems-demo-admin.localhost/dashboard) 
 * `sh ems.sh config_push local` will setup elasticms's configuration, content types, documents & so forth.
     * The script will ask for the `demo`'s password you just defined

It's all set: [elasticms-admin](http://local.ems-demo-admin.localhost/dashboard)

Check the [web preview](http://local.preview-ems-demo-web.localhost/) and the [web live](http://local.live-ems-demo-web.localhost/)

You can now login with your just defined password and the username `demo` in [elasticms](http://local.ems-demo-admin.localhost/dashboard).

## User

Some default user are created by the `create_users` command:
- author: can edit web documents
- publisher: can edit and publish web documents in live
- webmaster: can edit and publish all kind of documents


## Commands

List the available commands with this command: `sh ems.sh --help`

In this demo project only one `local` environment have been defined.

```
Usage: ems.sh <command> [options]

Commands:
    admin:        call the admin CLI for the given environment (corresponding to the admin-{environment} docker compose service)
    web:          call the web CLI for the given environment (corresponding to the web-{environment} docker compose service)
    create_users: create demo users in the given environment (corresponding to the admin-{environment} docker compose service)
    config_push:  load admin's configuration in the given environment (corresponding to the web-{environment} docker compose service)
```

There also a separated npm.sh script:

- `npm`: Run a npm console (in a docker run container)

## Troubleshooting

### FAQ

- The labels are not translated in the skeleton: clear the cache for the corresponding skeleton i.e. `sh ems.sh web local c:c`
- I do not see form submissions in the elasticms mini-CRM: Please update the auth-key in the form config 
- In some cases, updates in the webpack/npm application (`/src`) are not taken into account with the `npm run watch` command: docker compose doesn't allow npm to be notified on file changes. You should, or use the `npm run dev` command eachtime that you need. Or use a local installation of npm.

### Useful commands

- `docker compose ps` : List containers
- `docker compose logs -f` : View output from containers

### Useful links

 - [Traefik](http://localhost:8888) : An HTTP reverse proxy
 - [mailhog](http://mailhog.localhost) : A Mail catcher
 - [kibana](http://kibana.localhost) : A dev tools to query elasticsearch
 - [elasticsearch](http://es.localhost/_cluster/health) : The search engine, Verify that the status is `green`
 - [minio](http://minio.localhost) : A S3 like storage service 
 - [elasticms](http://local.ems-demo-admin.localhost/dashboard) : elasticms
 - Test the website:
   - [preview](http://local.preview-ems-demo-web.localhost/) : skeleton with preview's contents 
   - [live](http://local.live-ems-demo-web.localhost/) : skeleton with live's contents
 - Debug issue with varnish:
   - [preview nocache](http://local.preview-ems-demo-web-nocache.localhost/) : skeleton with preview's contents without cache (varnish is bypassed)
   - [live nocache](http://local.live-ems-demo-web-nocache.localhost/) : skeleton with live's contents without cache (varnish is bypassed)
