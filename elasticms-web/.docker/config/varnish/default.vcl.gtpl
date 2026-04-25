vcl 4.0;

import std;

backend default {
  .host = "127.0.0.1";
  .port = "9000";
  .probe = {
      .request =
        "{{ .Env.VARNISH_VCL_BACKEND_PROBE_REQUEST_HTTP_METHOD }} {{ .Env.VARNISH_VCL_BACKEND_PROBE_REQUEST_HTTP_URI }} {{ .Env.VARNISH_VCL_BACKEND_PROBE_REQUEST_HTTP_VERSION }}"
        "Host: {{ .Env.VARNISH_VCL_BACKEND_PROBE_REQUEST_HOST }}"
        "Connection: close"
        "User-Agent: Varnish Health Probe";
      .timeout = {{ .Env.VARNISH_VCL_BACKEND_PROBE_TIMEOUT }};
      .interval = {{ .Env.VARNISH_VCL_BACKEND_PROBE_INTERVAL }};
      .window = {{ .Env.VARNISH_VCL_BACKEND_PROBE_WINDOW }};
      .threshold = {{ .Env.VARNISH_VCL_BACKEND_PROBE_THRESHOLD }};
  }
}

sub vcl_recv {

  if (req.http.{{ .Env.VARNISH_VCL_RECV_REQUEST_X_FORWARDED_PROTO_HEADER_NAME }} == "https" ) {
    set req.http.X-Forwarded-Port = "443";
  } else {
    set req.http.X-Forwarded-Port = "80";
  }

  //activate the render_esi responses
  set req.http.Surrogate-Capability = "ESI/1.0";

  if (std.healthy(default)) {
    // change the behavior for healthy backends: Cap grace to 10s
    set req.grace = 10s;
  }

  // Remove all cookies except the session ID.
  if (req.http.Cookie) {
    set req.http.Cookie = ";" + req.http.Cookie;
    set req.http.Cookie = regsuball(req.http.Cookie, "; +", ";");
    set req.http.Cookie = regsuball(req.http.Cookie, ";(PHPSESSID)=", "; \1=");
    set req.http.Cookie = regsuball(req.http.Cookie, ";[^ ][^;]*", "");
    set req.http.Cookie = regsuball(req.http.Cookie, "^[; ]+|[; ]+$", "");

    if (req.http.Cookie == "") {
      // If there are no more cookies, remove the header to get page cached.
      unset req.http.Cookie;
    }
  }
  unset req.http.x-cache;
}

sub vcl_backend_response {
  set beresp.ttl = {{ .Env.VARNISH_VCL_BACKEND_RESPONSE_TTL }};
  set beresp.grace = {{ .Env.VARNISH_VCL_BACKEND_RESPONSE_GRACE }};

  // Check for ESI acknowledgement and remove Surrogate-Control header
  if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
    unset beresp.http.Surrogate-Control;
    set beresp.do_esi = true;
  }
}

sub vcl_hit {
  set req.http.x-cache = "hit";
}

sub vcl_miss {
  set req.http.x-cache = "miss";
}

sub vcl_pass {
  set req.http.x-cache = "pass";
}

sub vcl_pipe {
  set req.http.x-cache = "pipe uncacheable";
}

sub vcl_synth {
  set resp.http.x-cache = "synth synth";
}

sub vcl_deliver {
  if (obj.uncacheable) {
    set req.http.x-cache = req.http.x-cache + " uncacheable" ;
  } else {
    set req.http.x-cache = req.http.x-cache + " cached" ;
  }
  # (un)comment the following line to show the information in the response
  set resp.http.x-cache = req.http.x-cache;

  #For monitoring
  if (std.healthy(default)) {
    set resp.http.x-healthy = "true";
  }
  else {
    set resp.http.x-healthy = "false";
  }
}