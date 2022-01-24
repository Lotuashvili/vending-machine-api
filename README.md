# Vending Machine API

## Table of contents:

- [Pre-requisites](#pre-requisites)
- [Installation](#installation)
- [Testing](#testing)
- [API Endpoints](#api-endpoints)
- [Users](#users)

## Pre-requisites

This project requires PHP 8.0 or higher and MySQL 5.7 or higher.

## Installation

Run standard [Laravel installation](https://laravel.com/docs/8.x) procedures.

After local installation, run migrations and seeders.

```
php artisan migrate --seed
```

This will create default roles and users.

## Testing

After seeding initial data, run phpunit to run all tests.

```
vendor/bin/phpunit
```

## API Endpoints

API Endpoints with test data are defined in [Postman Collection file](Postman%20-%20API.json).

## Users

Two users are already created by a seeder:

**Buyer account:**

Email: `buyer@mvpfactory.co`

Password: `buyer123`

**Seller account:**

Email: `seller@mvpfactory.co`

Password: `seller123`
