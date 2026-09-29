"""Locust load profile for the ElasticMS demo website.

Replays the URLs discovered by scrape.py (urls.json) through Traefik, spoofing
the `Host: SITE_HOST` header so Traefik routes every request like edge traffic:
a -web-nocache host goes straight to web-local (nginx + php-fpm + ElasticMS), a
-web host through Varnish.

Driven by env (set by the Makefile / compose):
  LOCUST_HOST   base URL (http://traefik)
  SITE_HOST     Host header Traefik routes on
  URLS_FILE     discovered URLs (default /mnt/locust/urls.json)
"""
import json
import os
import random

from locust import HttpUser, between, task

SITE_HOST = os.environ.get("SITE_HOST", "local.preview-ems-demo-web-nocache.localhost")
URLS_FILE = os.environ.get("URLS_FILE", "/mnt/locust/urls.json")

# Fallback so a run never hard-fails if scrape.py has not run yet.
_FALLBACK = {"home": ["/fr", "/nl"], "listing": ["/fr/news"], "news": [], "pages": [], "assets": []}


def _load_urls():
    try:
        with open(URLS_FILE, encoding="utf-8") as fh:
            data = json.load(fh)
        if any(data.get(k) for k in ("home", "listing", "news", "pages")):
            return data
    except (OSError, ValueError):
        pass
    return _FALLBACK


URLS = _load_urls()


def _pick(bucket, fallback):
    return random.choice(URLS.get(bucket) or [fallback])


class DemoVisitor(HttpUser):
    wait_time = between(1, 3)

    def on_start(self):
        # Persistent Host header: Traefik routes every request to SITE_HOST.
        self.client.headers.update({"Host": SITE_HOST})

    @task(8)
    def news(self):
        self.client.get(_pick("news", "/fr/news"), name="/[locale]/news/[date]/[slug]")

    @task(5)
    def page(self):
        self.client.get(_pick("pages", "/fr"), name="/[locale]/[page]")

    @task(3)
    def listing(self):
        self.client.get(_pick("listing", "/fr/news"), name="/[locale]/news")

    @task(3)
    def home(self):
        self.client.get(_pick("home", "/fr"), name="/[locale]")

    @task(2)
    def asset(self):
        if URLS.get("assets"):
            self.client.get(random.choice(URLS["assets"]), name="/[asset]")
