#!/usr/bin/env python3
"""Discover GET-able URLs on the demo website and write them to urls.json.

Runs in a container on the demo network and crawls THROUGH Traefik: it requests
TARGET_HOST (http://traefik) with a `Host: SITE_HOST` header, so Traefik routes
it like real edge traffic -- to web-local for a -web-nocache host, to Varnish
for a -web one. Pure stdlib (urllib + html.parser): no extra package needed.

The output feeds locustfile.py, which replays these URLs under load.
"""
import html.parser
import json
import os
import sys
import urllib.error
import urllib.parse
import urllib.request

TARGET_HOST = os.environ.get("TARGET_HOST", "http://traefik").rstrip("/")
SITE_HOST = os.environ.get("SITE_HOST", "local.preview-ems-demo-web-nocache.localhost")
OUT = os.environ.get("OUT", "/data/urls.json")
MAX_URLS = int(os.environ.get("MAX_URLS", "120"))
MAX_DEPTH = int(os.environ.get("MAX_DEPTH", "3"))
# The skeleton is published in French and Dutch; each locale is a crawl root.
LOCALES = [loc.strip() for loc in os.environ.get("LOCALES", "fr,nl").split(",") if loc.strip()]
TIMEOUT = float(os.environ.get("TIMEOUT", "10"))

# Never crawl or replay these: Symfony's profiler and toolbar (APP_ENV=dev),
# the admin webhook, and the file/asset endpoints, which are fetched as assets.
SKIP_PREFIX = ("/_profiler", "/_wdt", "/_admin", "/file/", "/bundles/")
ASSET_PREFIX = ("/skeleton/", "/bundles/")


class LinkParser(html.parser.HTMLParser):
    def __init__(self):
        super().__init__()
        self.hrefs = []
        self.assets = []

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == "a" and attrs.get("href"):
            self.hrefs.append(attrs["href"])
        elif tag in ("script", "img") and attrs.get("src"):
            self.assets.append(attrs["src"])
        elif tag == "link" and attrs.get("href") and attrs.get("rel") == "stylesheet":
            self.assets.append(attrs["href"])


class NoRedirect(urllib.request.HTTPRedirectHandler):
    # Do NOT follow redirects: the new request would target `traefik` without our
    # Host header and 404. The Location is queued instead.
    def redirect_request(self, *args, **kwargs):
        return None


opener = urllib.request.build_opener(NoRedirect)


def fetch(path):
    """Return (status, location, body) for a GET on TARGET_HOST+path (Host spoofed)."""
    req = urllib.request.Request(TARGET_HOST + path, headers={"Host": SITE_HOST})
    try:
        resp = opener.open(req, timeout=TIMEOUT)
        status = resp.getcode()
        if 300 <= status < 400:
            return status, resp.headers.get("Location"), ""
        ctype = resp.headers.get("Content-Type", "")
        body = resp.read().decode("utf-8", "replace") if "html" in ctype else ""
        return status, None, body
    except urllib.error.HTTPError as e:
        return e.code, e.headers.get("Location"), ""
    except (urllib.error.URLError, OSError) as e:
        print(f"  ! {path}: {e}", file=sys.stderr)
        return 0, None, ""


def same_site_path(href, base_path):
    """Resolve href to a same-site '/path' (query dropped) or return None."""
    if href.startswith(("mailto:", "tel:", "javascript:", "#")):
        return None
    abs_url = urllib.parse.urljoin(f"http://{SITE_HOST}{base_path}", href)
    parts = urllib.parse.urlsplit(abs_url)
    if parts.netloc and parts.netloc != SITE_HOST:
        return None
    if not parts.path.startswith("/"):
        return None
    return parts.path


def page_path(href, base_path):
    path = same_site_path(href, base_path)
    if path is None or path.startswith(SKIP_PREFIX):
        return None
    # Stay within the published locales.
    if not any(path == f"/{loc}" or path.startswith(f"/{loc}/") for loc in LOCALES):
        return None
    return path


def categorize(path):
    parts = [p for p in path.split("/") if p]
    if len(parts) == 1:
        return "home"
    if len(parts) == 2 and parts[1] == "news":
        return "listing"
    if len(parts) > 2 and parts[1] == "news":
        return "news"
    return "pages"


def main():
    seen = set()
    queue = [(f"/{loc}", 0) for loc in LOCALES]
    collected = set()
    assets = set()

    while queue and len(collected) < MAX_URLS:
        path, depth = queue.pop(0)
        if path in seen:
            continue
        seen.add(path)

        status, location, body = fetch(path)
        if 300 <= status < 400 and location:
            loc = page_path(location, path)
            if loc and loc not in seen:
                queue.append((loc, depth))
            continue
        if status != 200:
            continue

        collected.add(path)
        parser = LinkParser()
        parser.feed(body)
        for src in parser.assets:
            asset = same_site_path(src, path)
            if asset and asset.startswith(ASSET_PREFIX):
                assets.add(asset)
        if depth >= MAX_DEPTH:
            continue
        for href in parser.hrefs:
            nxt = page_path(href, path)
            if nxt and nxt not in seen:
                queue.append((nxt, depth + 1))

    buckets = {"home": [], "listing": [], "news": [], "pages": [], "assets": sorted(assets)}
    for p in sorted(collected):
        buckets[categorize(p)].append(p)

    os.makedirs(os.path.dirname(OUT) or ".", exist_ok=True)
    with open(OUT, "w", encoding="utf-8") as fh:
        json.dump(buckets, fh, indent=2, ensure_ascii=False)

    total = sum(len(v) for k, v in buckets.items() if k != "assets")
    print(f"scraped {total} pages and {len(assets)} assets from {SITE_HOST} -> {OUT}")
    for k, v in buckets.items():
        print(f"   {k:8} {len(v)}")
    # An empty crawl means the site was unreachable: fail loudly.
    if total == 0:
        sys.exit(1)


if __name__ == "__main__":
    main()
