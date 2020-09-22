# phpipam-docker

Build scripts and dockerfiles for https://hub.docker.com/u/phpipam

---
## Intended Audience
As the typical users of phpIPAM (Network admins) have limited experience with LAMP stacks, these docker images provide a simpler method to create and maintain a working phpIPAM environment. Given the intended audience, simplicity is preferred over complexity and some advanced use cases are not supported with these images (e.g SSL)

Native SSL support can be achived by use of the many reverse-https-proxy docker images available on DockerHub.

For advanced use-cases phpIPAM can be installed in a VM by following the instructions found at https://phpipam.net/

## Source files, Issues & Pull Requests
Dockerfile build sources can be found at https://github.com/phpipam-docker/phpipam-docker

Issues and pull requests/patches for phpipam-docker can be found at https://github.com/phpipam-docker/phpipam-docker

Issues and pull requests for the phpIPAM application can be found at https://github.com/phpipam/phpipam

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

- `latest` Latest production release (1.4x).
- `1.4x`    Latest 1.4x release.
- `nightly` Nightly development snapshot (non-production).

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
    depends_on:
      - phpipam-mariadb

  phpipam-cron:
    image: phpipam/phpipam-cron:latest
    environment:
      - TZ=Europe/London
      - IPAM_DATABASE_HOST=phpipam-mariadb
      - IPAM_DATABASE_PASS=my_secret_phpipam_pass
      - SCAN_INTERVAL=1h
    restart: unless-stopped
    depends_on:
      - phpipam-mariadb

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
  phpipam-logo:
```

### Docker Swarm

...TODO

## Configuration

### Supported Docker Environment Variables

A subset of available phpIPAM configuration settings in [config.dist.php](https://github.com/phpipam/phpipam/blob/master/config.dist.php) can be configured via Docker Environment variables.

As an alternative to passing sensitive information via environment variables, _FILE may be appended to ENV_FILE marked environment variables, causing the initialization script to load the values for those variables from files present in the container. In particular, this can be used to load passwords from Docker secrets stored in /run/secrets/<secret_name> files.
```bash
$ docker ... -e IPAM_DATABASE_PASS_FILE=/run/secrets/ipam_database_password
```
| ENV                       | ENV_FILE | Default                 | WWW Container | CRON Container | Description                                                                  |
|---------------------------|:--------:|-------------------------|:-------------:|:--------------:|------------------------------------------------------------------------------|
| **TZ**                    |          | "UTC"                   |       ✅      |       ✅       | Time Zone (e.g "Europe/London")                                              |
| **IPAM_DATABASE_HOST**    |    ✅    | "127.0.0.1"             |       ✅      |       ✅       | MySQL database host                                                          |
| **IPAM_DATABASE_USER**    |    ✅    | "phpipam"               |       ✅      |       ✅       | MySQL database user                                                          |
| **IPAM_DATABASE_PASS**    |    ✅    | "phpipamadmin"          |       ✅      |       ✅       | MySQL database password                                                      |
| **IPAM_DATABASE_NAME**    |    ✅    | "phpipam"               |       ✅      |       ✅       | MySQL database name                                                          |
| **IPAM_DATABASE_PORT**    |    ✅    | 3306                    |       ✅      |       ✅       | MySQL database port                                                          |
| **IPAM_DATABASE_WEBHOST** |    ✅    | "localhost"             |       ✅      |       ✅       | MySQL allowed hosts                                                          |
| **PROXY_ENABLED**         |    ✅    | false                   |       ✅      |       ✅       | Use proxy                                                                    |
| **PROXY_SERVER**          |    ✅    | "myproxy.something.com" |       ✅      |       ✅       | Proxy server                                                                 |
| **PROXY_PORT**            |    ✅    | 8080                    |       ✅      |       ✅       | Proxy port                                                                   |
| **PROXY_USE_AUTH**        |    ✅    | false                   |       ✅      |       ✅       | Proxy authentication                                                         |
| **PROXY_USER**            |    ✅    | "USERNAME"              |       ✅      |       ✅       | Proxy username                                                               |
| **PROXY_PASS**            |    ✅    | "PASSWORD"              |       ✅      |       ✅       | Proxy password                                                               |
| **IPAM_DEBUG**            |    ✅    | false                   |       ✅      |       ✅       | Enable php/application debugging                                             |
| **IPAM_GMAPS_API_KEY**    |    ✅    | ""                      |       ✅      |                | Google Maps and Geocode API Key                                              |
| **IPAM_BASE**             |          | "/"                     |       ✅      |                | For proxy/loadbalancers. Path to access phpipam in site URL, http:/url/BASE/ |
| **SCAN_INTERVAL**         |          | "1h"                    |               |       ✅       | Network discovery job interval = 5m,10m,15m,30m,1h,2h,4h,6h,12h              |


### Docker Swarm Configs

All available phpIPAM configuration settings in [config.dist.php](https://github.com/phpipam/phpipam/blob/master/config.dist.php) can be configured via Docker Swarm Configs.

Using a swarm management tool of your choice (e.g Portainer/Rancher). Create a new swarm Config. Populate the Config with the contents of [config.dist.php](https://github.com/phpipam/phpipam/blob/master/config.dist.php) and adjust all available settings as required.

Assign the Config to the phpIPAM service and mount at `/phpipam/config.php`

For Docker deployments session storage should be set to **database**. `$session_storage = "database";` This is set automatically when configured via Docker Environment variables.

Assigning a swarm Config to `/phpipam/config.php` will disable the use of all Docker Environment variables except for TZ and SCAN_INTERVAL.

## License

GNU General Public License v3.0

## Maintainer

Gary Allan github@gallan.co.uk
