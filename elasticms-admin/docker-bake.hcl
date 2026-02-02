group "default" {
  targets = ["prd","dev"]
}

variable "EMS_VERSION" {
  default = "7.x-dev"
}

variable "DOCKER_IMAGE_NAME" {
  default = "elasticms/admin"
}

variable "DOCKER_IMAGE_TAG" {
  default = "snapshot"
}

variable "DOCKER_IMAGE_LATEST" {
  default = true
}

variable "GIT_HASH" {}

function "tag" {
  params = [version, tgt, githash]
  result = [
    version == "" ? "" : "${DOCKER_IMAGE_NAME}:${version}${tgt == "dev" ? "-dev" : ""}${githash == "" ? "" : "-${githash}"}",
  ]
}

# cleanTag ensures that the tag is a valid Docker tag
# see https://github.com/distribution/distribution/blob/v2.8.2/reference/regexp.go#L37
function "clean_tag" {
  params = [tag]
  result = substr(regex_replace(regex_replace(tag, "[^\\w.-]", "-"), "^([^\\w])", "r$0"), 0, 127)
}

# semver adds semver-compliant tag if a semver version number is passed, or returns the revision itself
# see https://semver.org/#is-there-a-suggested-regular-expression-regex-to-check-a-semver-string
function "semver" {
  params = [rev]
  result = __semver(_semver(regexall("^v?(?P<major>0|[1-9]\\d*)\\.(?P<minor>0|[1-9]\\d*)\\.(?P<patch>0|[1-9]\\d*)(?:-(?P<prerelease>(?:0|[1-9]\\d*|\\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\\.(?:0|[1-9]\\d*|\\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\\.[0-9a-zA-Z-]+)*))?$", rev)))
}

function "_semver" {
    params = [matches]
    result = length(matches) == 0 ? {} : matches[0]
}

function "__semver" {
    params = [v]
    result = v == {} ? [clean_tag(DOCKER_IMAGE_TAG)] : v.prerelease == null ? [v.major, "${v.major}.${v.minor}", "${v.major}.${v.minor}.${v.patch}"] : ["${v.major}.${v.minor}.${v.patch}-${v.prerelease}"]
}

target "default" {
  name = "${tgt}"

  matrix = {
    tgt = ["prd","dev"]
  }

  context    = "."
  dockerfile = "Dockerfile"
  target     = "${tgt}"

  platforms  = [
    "linux/amd64",
    "linux/arm64"
  ]

  labels = {
    "org.opencontainers.image.created"       = "${timestamp()}"
    "org.opencontainers.image.title"         = "elasticMS Admin"
    "org.opencontainers.image.description"   = "Admin of the elasticMS suite."
    "org.opencontainers.image.url"           = "https://www.elasticms.fgov.be"
    "org.opencontainers.image.source"        = "https://github.com/ems-project/elasticms-admin"
    "org.opencontainers.image.version"       = EMS_VERSION
    "org.opencontainers.image.revision"      = GIT_HASH
    "org.opencontainers.image.vendor"        = "elasticMS"
    "org.opencontainers.image.licenses"      = "LGPL-3.0"
    "be.fgov.elasticms.product"              = "elasticms"
    "be.fgov.elasticms.component"            = "admin"
    "be.fgov.elasticms.environment"          = tgt
    "be.fgov.elasticms.schema-version"       = "1.0"
  }

  tags = distinct(flatten([
      DOCKER_IMAGE_LATEST ? tag("latest", tgt, "") : [],
      GIT_HASH != "" && DOCKER_IMAGE_TAG != "snapshot" ? tag(clean_tag(DOCKER_IMAGE_TAG), tgt, "${substr(GIT_HASH, 0, 7)}") : [],
      DOCKER_IMAGE_TAG == "snapshot"
        ? [tag("snapshot", tgt, "")]
        : [for v in semver(DOCKER_IMAGE_TAG) : tag(v, tgt, "")]
    ])
  )

  attest = [
    {
      type = "provenance"
      mode = "max"
    },
    {
      type = "sbom"
    }
  ]

}