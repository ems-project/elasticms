# Environments

Environments are used to associate HTTP base urls to a specific config. It's usually defined in the EMSCH_ENVS skeleton environments variable. 

## Basic example
 ```json
    {
      "preview": {
        "regex": "/.*/",
        "alias": "demo_preview"
      }
    }
```

In this example every request are associated to the demo_preview elasticsearch alias

## Other options 

### remote_cluster

Allow to refer a [remote cluster](https://www.elastic.co/guide/en/elasticsearch/reference/6.8/modules-cross-cluster-search.html) on which elasticsearch queries will be made. If not define queries will be made on the defined cluster itself.

I.e.:

```json
{
  "preview-nl": {
    "regex": "/.*rva.*/",
    "request": {
      "_locale": "nl"
    },
    "alias": "demo_preview",
    "backend": "${BACKEND_URL}",
    "remote_cluster": "cluster_es7_test"
  },
  "demo_preview-de": {
    "regex": "/.*lfa.*/",
    "request": {
      "_locale": "de"
    },
    "alias": "demo_preview",
    "backend": "${BACKEND_URL}",
    "remote_cluster": "cluster_es7_test"
  },
  "preview-fr": {
    "regex": "/.*/",
    "request": {
      "_locale": "fr"
    },
    "alias": "demo_preview",
    "backend": "${BACKEND_URL}",
    "remote_cluster": "cluster_es7_test"
  }
}
```