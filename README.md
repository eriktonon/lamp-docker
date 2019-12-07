# LAMP built with Docker Compose

## Versions 
* PHP Version 7.0.33
* Apache Version 2.4
* MySQL version 8.0

## Config default  
* MySQL MySQL database and password settings are inside the .env file
* Apache2 vhost mapping are within .config / vhosts
* PHP a php file was left in the index to do some stack testing

## Installation

```shell 
git clone https://github.com/eriktonon/lamp-docker
cd lamp-docker
git checkout 7.0.33
docker-compose up -d
```

You can access it via http://localhost.

