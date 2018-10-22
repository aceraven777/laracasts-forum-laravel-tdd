# Council

This is an open source forum that was built and maintained at Laracasts.com

## Installation

### Step 1.

> To run this project, you must have PHP 7 installed as a prerequisite.

Begin by cloning this repository to your machine, and isntalling all Composer dependencies.

```bash
git clone git@github.com:aceraven777/laracasts-forum-laravel-tdd.git
cd council && composer install
php artisan key:generate
cp .env.example .env
```

### Step 2.

Next, create a new database and reference its name and username/password within the project's `.env` file. In the example below, we've named the database, "council".

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=council
DB_USERNAME=homestead
DB_PASSWORD=secret
```

Then, migrate your database to create tables.

```bash
php artisan migrate
```

### Step 3.

Until an administration portal is available, manually insert any number of "channels" (think of these as forum categories) into the "channels" table in your database.

Once finished, clear your server cache, and you're all set to go!

```bash
php artisan cache:clear
```

### Step 4.

Use your forum! Visit http://council.test/threads to create a new account and publish your first thread.

## Donate

[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Q4XLBV46V3958)