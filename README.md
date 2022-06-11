## Crypto SportsBook

### Installing

To get the project running you will need to install the dependencies
and also run the database seeders.

This project was built on top of [Laravel Framework](https://laravel.com/) and uses
[Laravel Sail](https://laravel.com/docs/8.x/sail) for development environment.

#### Project dependencies

- PHP 8.0 or higher
- composer 2.0 or higher
- Docker
- docker-compose
- Nodejs + yarn

Firstly you will need to install the composer dependencies by running the command bellow:

```shell
composer install
```

Then we will install NPM dependencies, which will allow [Laravel Octane to reload the server workers](https://laravel.com/docs/8.x/octane#reloading-the-workers)
on file changes by using Chokidar. To do so we will use yarn to install the dependencies.

```shell
yarn
```

Now you will need to create a **.env** file with all the required environment vars.
You can just make copy from **.env.example** and fill the new **.env** file with the right values.

**Database vars on .env.example are already ready for using Laravel Sail, so you will don't need to change this**

To make a copy you can just run:
```shell
cp .env.example .env
```

Once all dependencies have been installed and .env filled with the right values 
we will generate our application key required by Laravel by running:

```shell
php artisan key:generate
```

Now we will run Laravel Sail to build the containers and get them running. To get more info
about Laravel Sail check the [docs](https://laravel.com/docs/8.x/sail)

First, make the sail file executable
```shell
sudo chmod +x ./sail
```
and then run the containers
```shell
./sail up
```

Or to run the containers in detached mode use this instead:

```shell
./sail up -d
```

if you want to stop the containers (in detached mode) run the following
```shell
./sail down
```

#### Database Setup

Now all containers should be up and running, then it's time to run
the database seeders and insert all the data the project needs to work as expected.
**As mentioned above you don't need to define database env vars to use** _Sail_ **if you make a copy from .env.example.
So you just need to run the migrations.**

First, you should migrate the database tables:

```shell
./sail artisan migrate
```
If everything worked as expected you should now run the database seeders.
The command bellow will run the /database/seeders/DatabaseSeeder.php file which will insert all the data needed.

```shell
./sail artisan db:seed
```

Now we have everything we need on our database.

#### Laravel Passport

All the authentication flow Login/Register was built on top of Laravel Passport
which is a package to build API authentication with OAuth2. To get the authentication working
you will need to create the Laravel Passport password client which will be
used on our authentication flow from the SPA.

To create the Passport password client you should run the following command:

```shell
./sail artisan passport:client --password
```

Now you should fill the **PASSPORT_PASSWORD_CLIENT_ID** and **PASSPORT_PASSWORD_CLIENT_SECRET**
vars on your .env file with the result of the command above. The **_PASSPORT_PASSWORD_CLIENT_ID_** value should be **1**
if you are creating a fresh database.
