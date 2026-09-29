vcl 4.1;

import reqwest;
import std;

# The backend comes from the environment, so each stack points this one VCL at
# its own website: VARNISH_BACKEND_HOST is http://host.docker.internal:8882 for
# the dev server of the infra stack, http://web-local:9000 in the demo.
backend default none;

sub vcl_init {
    if (!std.getenv("VARNISH_BACKEND_HOST") || std.getenv("VARNISH_BACKEND_HOST") !~ "^https?://") {
        return (fail("VARNISH_BACKEND_HOST must be set to an http:// or https:// URL"));
    }
    # follow = 0: a redirect is the website's answer to the client, not
    # something for Varnish to chase.
    new website = reqwest.client(base_url = std.getenv("VARNISH_BACKEND_HOST"), follow = 0, timeout = 30s);
}

# ACL for BAN requests
acl purge {
    "localhost";
    "127.0.0.1";
    "host.docker.internal";
    "172.16.0.0"/12;  # Docker range
}

# Optional : token to allow BAN request
# Example: curl -X BAN http://varnish.localhost/ -H "X-Invalidate-Token: devsecret" -H "X-Cache-Tags: 123"
sub vcl_recv {
    set req.backend_hint = website.backend();

    # --- PURGE by URL ---
    if (req.method == "PURGE") {
        if (!(client.ip ~ purge) || req.http.X-Invalidate-Token != "devsecret") {
            return (synth(405, "Not allowed."));
        }
        return (purge);
    }

    # --- BAN by tag ---
    if (req.method == "BAN") {
        if (!(client.ip ~ purge) || req.http.X-Invalidate-Token != "devsecret") {
            return (synth(405, "Not allowed."));
        }
        if (!req.http.X-Cache-Tags) {
            return (synth(400, "Missing X-Cache-Tags header."));
        }
        ban("obj.http.X-Cache-Tags ~ " + req.http.X-Cache-Tags);
        return (synth(200, "Banned."));
    }

    # Only GET and HEAD can be put in cache
    if (req.method != "GET" && req.method != "HEAD") {
        return (pass);
    }

    # Don't put in cache if Cookie or Authorization header
    if (req.http.Authorization || req.http.Cookie) {
        return (pass);
    }
}

sub vcl_backend_response {
    # Bypass if not cacheable
    if (beresp.ttl <= 0s
        || beresp.http.Surrogate-Control ~ "no-store"
        || beresp.http.Cache-Control ~ "no-cache|no-store"
        || beresp.status >= 500) {
        set beresp.uncacheable = true;
        set beresp.ttl = 120s;
        return (deliver);
    }

    # Let ElasticMS control caching through Cache-Control / s-maxage
    return (deliver);
}

sub vcl_deliver {
    # Add a debug header
    if (obj.hits > 0) {
        set resp.http.X-Cache = "HIT";
    } else {
        set resp.http.X-Cache = "MISS";
    }
}
