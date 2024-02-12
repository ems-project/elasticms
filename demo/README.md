# Elasticms demo

A demo elasticms local setup with docker compose.

## TL;DR

Open a terminal and run the following commands:

```bash
make start
make status ## wait about a minute until elasticms_demo-web-local-1 is healthy
make init
```

* Go to the admin: http://local.ems-demo-admin.localhost (login: demo/demo)
* Go to the web/skeleton: http://local.preview-ems-demo-web.localhost

## Links

- [Traefik](http://localhost:8888) : An HTTP reverse proxy
- [mailhog](http://mailhog.localhost) : A Mail catcher
- [kibana](http://kibana.localhost) : A dev tools to query elasticsearch
- [elasticsearch](http://es.localhost/_cluster/health) : The search engine, Verify that the status is `green`
- [minio](http://minio.localhost) : A S3 like storage service
- [elasticms admin](http://local.ems-demo-admin.localhost/dashboard) : elasticms
  - [admin debug](http://local.ems-demo-admin-dev.localhost/login): Useful to develop admin's template
- Test the website:
  - [preview](http://local.preview-ems-demo-web.localhost/) : skeleton with preview's contents
  - [live](http://local.live-ems-demo-web.localhost/) : skeleton with live's contents
- Debug issue with varnish:
  - [preview nocache](http://local.preview-ems-demo-web-nocache.localhost/) : skeleton with preview's contents without cache (varnish is bypassed)
  - [live nocache](http://local.live-ems-demo-web-nocache.localhost/) : skeleton with live's contents without cache (varnish is bypassed)

## MakeFile

Run `make` for retrieving information about all make targets.

- Create a new user cli
  ```bash 
  make admin/"emsco:user:create"
  ```
- Show all available commands for web and/or admin
  ```bash 
  make admin/"list"
  make web/"list"
  ```
- Run npm command
  ```bash
  make npm/"list"
  make npm/"install <package_name>"
  ```

## Troubleshooting

- The labels are not translated in the skeleton: 
  > clear the cache for the corresponding skeleton i.e. `make cache-clear`
- Form submissions are not visible in the elasticms mini-CRM: 
  > Generate a `.env.local` and this `EMSF_AUTHKEY=AUTH_KEY` with a authkey that 
  > you can generate in the [user datatable view](http://local.ems-demo-admin.localhost/user/)
  > and run `make restart`
- I want to use a different ELK stack version
  > If you want to use elasticsearch 8 instead of elasticsearch 7, define this variable first:
  > ```bash
  > export ELK_VERSION=elk8
  > ```
  > Available stacks:
  > * `elk7`: elasticsearch 7.17.7
  > * `elk8`: elasticsearch 8.5.3
  > * `os2`: OpenSearch 2.4.1

## Requirements

You need docker compose (or an alternative as Podman) on a running Linux system
(or WSL if you are under Windows). It should also work with Docker Desktop,
tell us if you face issues with it.

It's recommended to allow at least 6GB of memory to docker.

The following ports must be available:
* 8888: Traefik UI
* 80: Web HTTP
* 443: Web HTTPS

## Resources

* [Documentation](https://ems-project.github.io/)
* [Report issues](https://github.com/ems-project/elasticms/issues) and
  [send Pull Requests](https://github.com/ems-project/elasticms/pulls)
  in the [elasticMS mono repository](https://github.com/ems-project/elasticms)