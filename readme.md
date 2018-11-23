# Council [![Build Status](https://travis-ci.org/aceraven777/laracasts-forum-laravel-tdd.svg?branch=master)](https://travis-ci.org/aceraven777/laracasts-forum-laravel-tdd)

This is an open source forum that was built and maintained at Laracasts.com.

## Donate

[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Q4XLBV46V3958)

## Installation

### Step 1.

> To run this project, you must have PHP 7 installed as a prerequisite.

Begin by cloning this repository to your machine, and installing all Composer & NPM dependencies.

```bash
git clone git@github.com:aceraven777/laracasts-forum-laravel-tdd.git
cd council && composer install && npm install
php artisan council:install
npm run dev
```

### Step 2.

Until an administration portal is available, manually insert any number of "channels" (think of these as forum categories) into the "channels" table in your database.

1. Visit: http://council.test/register and register an account.
1. Edit `config/council.php`, adding the email address of the account you just created.
1. Visit: http://council.test/admin/channels and add at least one channel.