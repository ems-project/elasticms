# ElasticMS Admin

The ElasticMS Admin is a headless CMS based on Symfony, elasticsearch and Boostrap.

Resources
---------

* [Documentation](https://ems-project.github.io/#/./elasticms-admin/index)
* [Report issues](https://github.com/ems-project/elasticms/issues) and
  [send Pull Requests](https://github.com/ems-project/elasticms/pulls)
  in the [elasticMS mono repository](https://github.com/ems-project/elasticms)

Docker image
------------

`make build-image` (or `make bake-image`) builds the image: `build-app` installs
the Composer and npm dependencies in a base-php builder container, then the `prd` and
`dev` targets are built.

The build also works from a network that reaches the internet only through a
proxy, including a TLS-inspecting one. Nothing changes unless these variables are
set in the calling shell:

| Variable | Effect |
|----------|--------|
| `HTTP_PROXY`, `HTTPS_PROXY`, `NO_PROXY` (and lower case) | Passed to the image build and to the Composer / npm containers. |
| `CUSTOM_CA_BUNDLE` | Absolute path to a PEM file holding the corporate CA. It is added to the trust store, never substituted for it, and never persisted in the image. |
| `NO_CACHE=true` | Forces a cache-less rebuild. Not needed for a CA change, which already rebuilds the CA layer. |
| `ENABLE_ATTESTATIONS=false` | Skips the provenance and SBOM attestations when the builder cannot produce them. |
