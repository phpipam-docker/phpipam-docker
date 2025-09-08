# phpipam-docker

Build scripts and dockerfiles for [https://hub.docker.com/u/phpipam](https://hub.docker.com/u/phpipam)

---

## Intended Audience

As the typical users of phpIPAM (Network admins) have limited experience with LAMP stacks, these docker images provide a simpler method to create and maintain a working phpIPAM environment. Given the intended audience, simplicity is preferred over complexity and some advanced use cases are not supported with these images.

Native SSL support can be achieved by use of the many reverse-https-proxy docker images available on DockerHub. See HAProxy example below.

For advanced use-cases phpIPAM can be installed in a VM by following the instructions found at [https://phpipam.net](https://phpipam.net)

## Source files, Issues & Pull Requests

Dockerfile build sources can be found at [https://github.com/phpipam-docker/phpipam-docker](https://github.com/phpipam-docker/phpipam-docker)

Issues and pull requests/patches for phpipam-docker can be found at [https://github.com/phpipam-docker/phpipam-docker](https://github.com/phpipam-docker/phpipam-docker)

Issues and pull requests for the phpIPAM application can be found at [https://github.com/phpipam/phpipam](https://github.com/phpipam/phpipam)

## Container Images

- `phpipam-www` Frontend Apache/PHP container.
- `phpipam-cron` cron container for scheduled network discovery jobs.

Multiple replicas of the **phpipam-www** container can be deployed and load-balanced.

Only a single running replica of the **phpipam-cron** container should be deployed.

## Permissions & Capabilities

Rootless docker is not supported.

When running under Docker, NET_ADMIN and NET_RAW container capabilities are required for ping and SNMP functionality.

When running under Kubernetes, set allowPrivilegeEscalation=true

## Supported Tags

- `latest` Tracks the latest stable release + Alpine Linux security updates.
- `1.7x`    Tracks the 1.7 git maintenance branch + Alpine Linux security updates.
- `1.6x`    Tracks the 1.6 git maintenance branch + Alpine Linux security updates (obsolete).
- `1.5x`    Tracks the 1.5 git maintenance branch + Alpine Linux security updates (obsolete).
- `1.4x`    Tracks the 1.4 git maintenance branch + Alpine Linux security updates (obsolete).
- `nightly` Nightly git development snapshot (non-production).
- `v1.7.x / v1.6.y / v1.5.y` Static snapshots, no Alpine Linux security updates.

## Usage

### Docker Standalone

Example full stack deployment via docker-compose.

Save and edit the the below configuration as __docker-compose.yml__ and run `docker-compose -p phpIPAM up -d` from the same directory.

```yaml
# WARNING: Replace the example passwords with secure secrets.
# WARNING: 'my_secret_phpipam_pass' and 'my_secret_mysql_root_pass'

version: '3'

services:
  phpipam-web:
    image: phpipam/phpipam-www:latest
    ports:
      - "80:80"
    environment:
      - TZ=Europe/London
      - IPAM_DATABASE_HOST=phpipam-mariadb
      - IPAM_DATABASE_PASS=my_secret_phpipam_pass
      - IPAM_DATABASE_WEBHOST=%
    restart: unless-stopped
    volumes:
      - phpipam-logo:/phpipam/css/images/logo
      - phpipam-ca:/usr/local/share/ca-certificates:ro
    depends_on:
      - phpipam-mariadb
    cap_add:
      - NET_ADMIN
      - NET_RAW

  phpipam-cron:
    image: phpipam/phpipam-cron:latest
    environment:
      - TZ=Europe/London
      - IPAM_DATABASE_HOST=phpipam-mariadb
      - IPAM_DATABASE_PASS=my_secret_phpipam_pass
      - SCAN_INTERVAL=1h
    restart: unless-stopped
    volumes:
      - phpipam-ca:/usr/local/share/ca-certificates:ro
    depends_on:
      - phpipam-mariadb
    cap_add:
      - NET_ADMIN
      - NET_RAW

  phpipam-mariadb:
    image: mariadb:latest
    environment:
      - MYSQL_ROOT_PASSWORD=my_secret_mysql_root_pass
    restart: unless-stopped
    volumes:
      - phpipam-db-data:/var/lib/mysql

volumes:
  phpipam-db-data:
  phpipam-logo:
  phpipam-ca:
```

### Docker External MySQL Server

Example external MySQL server deployment via docker-compose.

Save and edit the the below configuration as __docker-compose.yml__ and run `docker-compose -p phpIPAM up -d` from the same directory.

```yaml
version: '3'

services:
  phpipam-web:
    image: phpipam/phpipam-www:latest
    ports:
      - "80:80"
    environment:
      - TZ=Europe/London
      - IPAM_DATABASE_HOST=my.database.server
      - IPAM_DATABASE_USER=existing_username
      - IPAM_DATABASE_PASS=existing_password
      - IPAM_DATABASE_NAME=existing_db_name
    restart: unless-stopped
    volumes:
      - phpipam-logo:/phpipam/css/images/logo
      - phpipam-ca:/usr/local/share/ca-certificates:ro
    cap_add:
      - NET_ADMIN
      - NET_RAW

  phpipam-cron:
    image: phpipam/phpipam-cron:latest
    environment:
        - TZ=Europe/London
        - IPAM_DATABASE_HOST=my.database.server
        - IPAM_DATABASE_USER=existing_username
        - IPAM_DATABASE_PASS=existing_password
        - IPAM_DATABASE_NAME=existing_db_name
        - SCAN_INTERVAL=1h
    restart: unless-stopped
    volumes:
      - phpipam-ca:/usr/local/share/ca-certificates:ro
    cap_add:
      - NET_ADMIN
      - NET_RAW

volumes:
  phpipam-logo:
  phpipam-ca:
```

## Configuration

### Supported Docker Environment Variables

A subset of available phpIPAM configuration settings in [config.dist.php](https://github.com/phpipam/phpipam/blob/master/config.dist.php) can be configured via Docker Environment variables.

As an alternative to passing sensitive information via environment variables, _FILE may be appended to environment variables marked 📂, causing the initialization script to load the values for those variables from files present in the container. In particular, this can be used to load passwords from Docker secrets stored in /run/secrets/<secret_name> files.

```bash
docker ... -e IPAM_DATABASE_PASS_FILE=/run/secrets/ipam_database_password
```

| ENV                           | Default                 | WWW/CRON Container | Description                                                                                     |
|-------------------------------|-------------------------|:------------------:|-------------------------------------------------------------------------------------------------|
| **TZ**                        | "UTC"                   |        ✅ ✅         | Time Zone (e.g "Europe/London")                                                                 |
| **IPAM_DISABLE_INSTALLER** 📂 | "false"                 |        ✅ ❌         | Disables installation helper scripts (v1.6.1+) \*\*                                             |
| **IPAM_DATABASE_HOST** 📂     | "127.0.0.1"             |        ✅ ✅         | MySQL database host                                                                             |
| **IPAM_DATABASE_USER** 📂     | "phpipam"               |        ✅ ✅         | MySQL database user                                                                             |
| **IPAM_DATABASE_PASS** 📂     | "phpipamadmin"          |        ✅ ✅         | MySQL database password                                                                         |
| **IPAM_DATABASE_NAME** 📂     | "phpipam"               |        ✅ ✅         | MySQL database name                                                                             |
| **IPAM_DATABASE_PORT** 📂     | 3306                    |        ✅ ✅         | MySQL database port                                                                             |
| **IPAM_DATABASE_WEBHOST** 📂  | "localhost"             |        ✅ ✅         | MySQL allowed hosts                                                                             |
| **IPAM_BASE**                 | "/"                     |        ✅ ❌         | For reverse-proxy/load-balancers. Path to access phpipam in site URL, http:/url/BASE/           |
| **IPAM_TRUST_X_FORWARDED** 📂 | "false"                 |        ✅ ❌         | Trust and use X_FORWARDED_* headers set by reverse-proxies/load-balancers (v1.6.0+)  \*         |
| **PROXY_ENABLED** 📂          | false                   |        ✅ ✅         | Use proxy                                                                                       |
| **PROXY_SERVER** 📂           | "myproxy.something.com" |        ✅ ✅         | Proxy server                                                                                    |
| **PROXY_PORT** 📂             | 8080                    |        ✅ ✅         | Proxy port                                                                                      |
| **PROXY_USE_AUTH** 📂         | false                   |        ✅ ✅         | Proxy authentication                                                                            |
| **PROXY_USER** 📂             | "USERNAME"              |        ✅ ✅         | Proxy username                                                                                  |
| **PROXY_PASS** 📂             | "PASSWORD"              |        ✅ ✅         | Proxy password                                                                                  |
| **IPAM_DEBUG** 📂             | false                   |        ✅ ✅         | Enable php/application debugging                                                                |
| **OFFLINE_MODE** 📂           | false                   |        ✅ ❌         | Disable server-side Internet requests, avoid timeouts with restricted Internet access (v1.5.0+) |
| **COOKIE_SAMESITE** 📂        | "Lax"                   |        ✅ ❌         | Cookie security policy = None,Lax,Strict. "None" requires HTTPS. (v1.4.5+)                      |
| **IPAM_FOOTER_MESSAGE** 📂    | ""                      |        ✅ ❌         | Custom message included in the footer of every page (v1.6.0+)                                   |
| **IPAM_GMAPS_API_KEY** 📂     | ""                      |        ✅ ❌         | Google Maps and Geocode API Key. (Removed in v1.5.0, replaced by OpenStreetMap)                 |
| **SCAN_INTERVAL**             | "1h"                    |        ❌ ✅         | Network discovery job interval = 5m,10m,15m,30m,1h,2h,4h,6h,12h                                 |

**\*** Enabling **IPAM_TRUST_X_FORWARDED** without filtering request from end-clients can result in security issues. Ensure the headers are filtered or overwritten by the reverse-proxy/loadbalancer and can not be set by the clients.

**\*\*** Please disable installation helper scripts by setting **IPAM_DISABLE_INSTALLER = 1** after initial setup is complete.

### In Container config.php

From v1.5.0 the IPAM_CONFIG_FILE environment variable can be used load a configuration file located in a persistent volume.

When IPAM_CONFIG_FILE is configured the following environment variables are available. All other enviroment variables are ignored and must be configured
manually in the config.php file if required.

| ENV                  | Default | WWW/CRON Container | Description                                                                        |
|----------------------|---------|:------------------:|------------------------------------------------------------------------------------|
| **TZ**               | "UTC"   |        ✅ ✅       | Time Zone (e.g "Europe/London")                                                    |
| **IPAM_CONFIG_FILE** | ""      |        ✅ ✅       | Full path to the config file (e.g "/config/config.php")<br>Must end .php (v1.5.0+) |
| **SCAN_INTERVAL**    | "1h"    |        ❌ ✅       | Network discovery job interval = 5m,10m,15m,30m,1h,2h,4h,6h,12h                    |

_NOTE: When load-balancing multiple instances set_ `$session_storage = "database";` _to ensure consistent PHP session information across all containers._

## Trusted CAs

`update-ca-certificates` is run at startup, all certificates found below `/usr/local/share/ca-certificates` are included as implicitly trusted. Mount persistent storage at this location with custom trusted CAs in PEM format.

``` bash
/usr/local/share/ca-certificates # ls
ACME_ROOT.crt ACME_INTERMEDIATE_1.crt ACME_INTERMEDIATE_2.crt
```

## HAProxy SSL Example

Example configuration to use HAProxy as a reverse HTTPS proxy for phpIPAM.

Create the HAProxy container.

```bash
docker run -d -p 443:443 -p 80:80 --name HAProxy --restart always -v haproxy_ssl:/etc/ssl/certs -v haproxy_cfg:/usr/local/etc/haproxy haproxy:latest
```

Create the /usr/local/etc/haproxy/haproxy.cfg configuration file inside the container.

```text
# Example /usr/local/etc/haproxy/haproxy.cfg

global
  daemon
  maxconn 256

resolvers mydns
  # Replace with the IP addresses of your internal DNS servers
  nameserver ns1 192.168.1.53:53
  nameserver ns2 192.168.2.53:53
  accepted_payload_size 8192

defaults
  mode http
  timeout connect 5000ms
  timeout client  5000ms
  timeout server 60000ms
  option forwardfor

frontend phpipam-rp
  bind *:80
  bind *:443 ssl crt /etc/ssl/certs
  http-request redirect scheme https code 301 unless { ssl_fc }
  default_backend phpipam-web
  http-request del-header X-Forwarded-For

backend phpipam-web
  # Replace phpipam.local with the internal DNS name of your phpipam container.
  server s1 phpipam.local:80 check resolvers mydns init-addr none
  http-request set-header X-Forwarded-Uri %[url]
  http-request set-header X-Forwarded-Port %[dst_port]
  http-request add-header X-Forwarded-Proto https if { ssl_fc }
```

HAProxy will load your full-chain certificate and key files mounted at /etc/ssl/certs inside the container.

```bash
haproxy@HAProxy:/etc/ssl/certs$ ls
ipam.crt  ipam.crt.key
```

Restart the HAProxy container and check the container logs for issues.

```bash
docker restart HAProxy
docker container logs HAProxy
```

## License

GNU General Public License v3.0

## Maintainer

Gary Allan github@gallan.co.uk
