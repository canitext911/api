# CANiTEXT911 API

Lumen-powered backend for [api.canitext911.us](https://api.canitext911.us).

## Requirements
- Basic [Lumen requirements](https://lumen.laravel.com/docs/5.7#server-requirements)
- PHP >= 7.1.3
- Postgres

## Environment
``` dotenv
# database config
DB_CONNECTION=pgsql
DATABASE_URL=postgres://username:password@host:5432/database

# service endpoints
CIT_IP_LOCATION_ENDPOINT='http://ip-api.com/json/${IP_ADDR}'
CIT_TEXT_REGISTRY_ENDPOINT='https://www.fcc.gov/file/12285/download'
```

## Setup

``` bash
# install dependencies
composer install

# run migrations
php artisan migrate

# fetch psap registry
php artisan psap:index

# serve
php -S localhost:8000 -t public
```

## Contributions
Thanks for considering contributing! Please open an issue prior to submitting any pull requests.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
