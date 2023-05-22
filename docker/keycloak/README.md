# Keycloak

Setup a keycloak

```bash
docker compose up -d
```
Check if available http://localhost:8083/admin/master/console
Login: admin:changeme

```bash
docker compose exec keycloak sh /opt/keycloak/bin/kc.sh import --dir /data
docker compose up -d --force-recreate
```

Admin: http://localhost:8083/admin/master/console/#/elasticms/users
> admin  changeme

Users: http://localhost:8083/realms/elasticms/account
> user1@example.com changeme
> 
> user2@example.com changeme

## Export keycloak
Update the backup

```bash
docker compose exec keycloak sh /opt/keycloak/bin/kc.sh export --dir /data --users same_file --realm elasticms
```