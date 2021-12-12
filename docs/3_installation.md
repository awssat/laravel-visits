# Installation

To get started with Laravel Visits, use Composer to add the package to your project's dependencies:

```bash
composer require awssat/laravel-visits
```

## Configurations

To adjust the package to your needs, you can publish the config file `config/visits.php` to your project's config folder using:

```bash
php artisan vendor:publish --provider="Awssat\Visits\VisitsServiceProvider" --tag=config
```

## Redis Configuration

If you are not using Redis as your default data engine, skip this.

By default `laravel-visits` doesn't use the default laravel redis configuration (see [issue #5](https://github.com/awssat/laravel-visits/issues/5))

To prevent any data loss add a new connection in `config/database.php`

```php
'laravel-visits' => [
    'host' => env('REDIS_HOST', '127.0.0.1'),
    'password' => env('REDIS_PASSWORD', null),
    'port' => env('REDIS_PORT', 6379),
    'database' => 3, // anything from 1 to 15, except 0 (or what is set in default)
],
```

and you can define your redis connection name in `config/visits.php`

```php
'connection' => 'laravel-visits'
```

## Eloquent (database) configuration

If you are using Redis as your default data engine, skip this.

Publish migration file, then migrate

```sh
php artisan vendor:publish --provider="Awssat\Visits\VisitsServiceProvider" --tag=migrations
```

```sh
php artisan migrate
```

## Package Configuration

Laravel Visits can be configured to act the way you like, `config/visits.php` is clear and easy to understand but here some explanation for its settings.

### config/visits.php settings explained

#### engine

```php
'engine' => 'redis',
```

Suported data engines are `redis`, and `eloquent` currently.
If you use `eloquent` then data will be stored in the default database (MySQL, SQLite or the one you are using)

#### connection

```php
'connection' => 'laravel-visits',
```

Currently only applies when using Redis as data engine. Check [Redis Configuration](#redis-configuration)

#### periods

```php
'periods' => [
    'day',
    'week',
    'month',
    'year',
],
```

By default, Visits of `day`, `week`, `month`, and `year` are recorded. But you can add or remove any of them as you like.

> **Note** supported periods can be found in [periods-options](8_clear-and-reset-values.html#periods-options.md)

> You can add `periods` to global_ignore setting to skip recording any of these periods.

#### keys_prefix

```php
'keys_prefix' =>  'visits',
```

A word that's appended to the begining of keys names. If you are using shared Redis database, it's important to keep this filled.

#### remember_ip

```php
'remember_ip' => 15 * 60, // seconds
```

Every distinct IP will only be recorded as one visit every 15 min (default).

#### always_fresh

```php
'always_fresh' => false,
```

## If you set this to `true`, then any [Visits Lists](7_visits-lists.md) won't be cached any will return a new generated list.

## We don't recommend enabling this feature as it's not good for performance.

#### ignore_crawlers

```php
'ignore_crawlers' => true,
```

By default, visits from search engines bots and any other recognizable bots are not recorded. By enabling this you allow visits from bots to be recoded.

#### global_ignore

```php
'global_ignore' => [],
```

By default, 'country', 'refer', 'periods', 'operatingSystem', and 'language' of a visitor are recoded. You can disable recoding any of them by adding them to the list.

---

<p align="left">
  Prev:  <a href="2_requirements.md">< Requirements</a> 
</p>

<p align="right">
  Next:  <a href="4_quick-start.md">Quick start ></a> 
</p>
