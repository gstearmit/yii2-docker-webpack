# Bootstrap for Yii2

[![Build Status](https://travis-ci.org/rkit/bootstrap-yii2.svg?branch=master)](https://travis-ci.org/rkit/bootstrap-yii2)
[![Code Coverage](https://scrutinizer-ci.com/g/rkit/bootstrap-yii2/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/rkit/bootstrap-yii2/?branch=master)
[![codecov.io](http://codecov.io/github/rkit/bootstrap-yii2/coverage.svg?branch=master)](http://codecov.io/github/rkit/bootstrap-yii2?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rkit/bootstrap-yii2/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/rkit/bootstrap-yii2/?branch=master)

## Features

- Users, Roles, Registration, Basic and social authorization
- [Settings](https://github.com/rkit/settings-yii2)
- [File Manager](https://github.com/rkit/filemanager-yii2)
- [Webpack for assets](https://webpack.github.io/)


## Soft

- PHP 7.1.x
- Node.js + NPM 5.x
- Composer

> For to setup development environment, you could use [Docker](./README.md#docker-for-development-environment)

## Installation

1. Cloning a repository
   ```
   git clone https://github.com/rkit/bootstrap-yii2.git
   cd bootstrap-yii2
   ```

2. Creating a project
   ```sh
   composer create-project
   ```

3. Checking requirements
   ```
   php requirements.php
   ```

4. Creating a new database and local config
   ```
   php yii create-local-config --path=@app/config/local/main.php
   ```
   > filling in the database settings in the *config/local/main.php*

5. Build application
   ```
   composer build
   ```

Access to the Control Panel
```
username: editor  
password: fghfgh
```

## Configuring Server

The application requires the document root to be set to the `web` directory.

## Development

### Debug mode

- Nginx Configuration
  ```nginx
  fastcgi_param APPLICATION_ENV development;
  ```

### Assets

- Watch mode (debug)
  ```
  npm run watch
  ```

- Build for production
  ```
  npm run build
  ```

### Tests

- [See docs](/tests/#tests)

### Coding Standard

- PHP Code Sniffer — [phpcs.xml](./phpcs.xml)
- ESLint — [.eslintrc](./.eslintrc)

### Docker for development environment

1. Install [Docker](https://www.docker.com/) and execute the first step of [installation](./README.md#installation)

2. Copy [.env.dist](./.env.dist) to `.env` and specify environment variables

3. Create and start containers
   ```sh
   docker-compose up -d
   ```

4. Follow the [installation](./README.md#installation) steps (skip the first step).  
   Run all commands through docker `docker-compose exec php`

## Configuring Server

- Nginx - [development config](./docker/nginx/conf.d/dev.conf)


---------- UPDATE DOCKER  21/08/2017------------------

1.0 $ docker-compose ps
        Name                       Command               State           Ports          
    ---------------------------------------------------------------------------------------
    bootstrapyii2_mysql_1   docker-entrypoint.sh mysqld      Up      0.0.0.0:3306->3306/tcp 
    bootstrapyii2_nginx_1   nginx -g daemon off;             Up      0.0.0.0:80->80/tcp     
    bootstrapyii2_php_1     docker-php-entrypoint php- ...   Up      9000/tcp               
    GSTEARMITs-MacBook-Air:bootstrap-yii2 gstearmit$ 
    
2.0 SQLSTATE[HY000] [2002] No such file or directory / Connection refused 
   https://github.com/dmstr/docker-yii2-app/issues/4
   EDIT: this is the solution
   
   In your config file docker-composer.yml
   
     mysql:
       image: 'mysql:latest'
       volumes:
           - ./docker/dbdata:/var/lib/mysql
       ports:
           - '3306:3306'
       restart: always
       environment:
          MYSQL_ROOT_PASSWORD: pass
          MYSQL_DATABASE: database
   In your project db.php
   
   return [
       'class' => 'yii\db\Connection',
       'dsn' => 'mysql:host=mysql;dbname=database',
       'username' => 'root',
       'password' => 'pass',
       'charset' => 'utf8',
   ];
   
3.0
    $ docker-compose up -d
    $ docker ps -a
    $ docker exec -it ace41426dfe3 /bin/bash
     # composer update
     
4.0
  Access to the Control Panel
  
  username: editor  
  password: fghfgh
  
5.0 See Document Test
  https://github.com/gstearmit/bootstrap-yii2/tree/master/tests#tests
      Tests
      
      Preparation
      
      --> Create bootstrap_yii2_tests database and run
      
      $ composer test:build
      Commands
      
          Run all tests $ composer test
          Run unit tests $ composer test:unit
          Run functional tests  $ composer test:functional
          Reconfigure modules of codeception  $ composer test:reconfig
          Run tests with coverage  $ composer test:coverage
          Show coverage dashboard  $ composer test:stats
