vcl 4.1;

backend default {
    .host = "host.docker.internal";
    .port = "8882";
    .first_byte_timeout = 30s;
    .between_bytes_timeout = 30s;
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
