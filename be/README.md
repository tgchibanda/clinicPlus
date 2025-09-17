# MyApi

A tutorial on how it was made is avaliable here

http://www.ernestmuroiwa.com/dockerized-laravel-rest-api/

http://www.ernestmuroiwa.com/laravel-rest-api/

**Go to the postman collection on this link to view the endpoints**

https://documenter.getpostman.com/view/8975783/TVYF8Ja3

MyApi app made with Laravel 8 and Mysql DB

# Requirements

-   Docker
-   Docker composer

To get started, make sure you have [Docker installed](https://docs.docker.com/docker-for-linux/install/) on your system, and then clone this repository.

# Setup

You need to clone the project to create a local copy on your system.
Run the following on your terminal:

```
git clone https://github.com/emuroiwa/myapi
```

Then change into the project's directory by running the following on your terminal:

```
cd myapi

```

# Configurations

After complete setup process you have to configure you database credentials. First copy `.env.example` as `.env`

```shell
cp .env.example .env
```

```shell
nano .env
```

Set up DB details

```php
APP_NAME=myapi
APP_ENV=dev
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=myapi
DB_USERNAME=root
DB_PASSWORD=root
```

Then you can span up your containers by running

```shell
docker-compose up -d
```

You containers should be up and running hopefully. Now we have to set directory permissions
Navigate out of the root folder

```shell
cd ..
```

Then run these commands

```shell
sudo chown -R $USER:www-data myapi/ -R
chmod 775 myapi/ -R
```

Return back to the root folder

```shell
cd ./myapi
```

You need to run `composer install` to install application dependencies

```shell
docker-compose exec app composer install
```

To generate key please run this:

```
docker-compose exec app php artisan key:generate
```

Now open `.env` file and write database informations. Then run migrate from you terminal

```shell
docker-compose exec app php artisan migrate
```

Now run the DB seeder

```shell
docker-compose exec app php artisan db:seed
```

Laravel passport config

```shell
docker-compose exec app php artisan passport:install
docker-compose exec app php artisan queue:work

```

# Running the project

Run the following on your on your terminal:

```
docker-compose exec app php artisan serve
```

and access the website on your local website with this url localhost:8000.

# PHPUnit Tests

In the app root folder run this command in the terminal

```shell
docker-compose exec app ./vendor/bin/phpunit
```

# Logs

In the app root folder run this command in the terminal

```shell
tail ./storage/logs/\*\* -f
```
