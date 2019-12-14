# Laravel Visits

![aravel-visits](https://i.imgur.com/xHAzl0G.png)


[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


## Introduction
Laravel Visits is a counter that can be attached to any model to track its visits with useful features like IP-protection and lists caching.


## Table of Contents
  * [Features](#features)
  * [Install](#install)
  * [Usage](#usage)
  * [Increments and Decrements](#increments-and-decrements)
  * [An item visits](#an-item-visits)
  * [A model class visits](#a-model-class-visits)
  * [Countries of visitors](#countries-of-visitors)
  * [Referers of visitors](#referers-of-visitors)
  * [Top or Lowest list per model type](#top-or-lowest-list-per-model-type)
  * [Reset and clear values](#reset-and-clear-values)
  * [Integration with Eloquent](#integration-with-eloquent)
  * [Changelog](#changelog)
  * [Contributing](#contributing)
  * [Credits](#credits)
  * [License](#license)



## Features
- A model item can have many types of recorded visits (using tags).
- It's not limited to one type of Model (like some packages that allow only User model).
- Record per visitors and not by vistis using IP detecting, so even with refresh, visit won't duplicate (can be changed from config). 
- Get Top/Lowest visits per a model.
- Get most visited countries, refs, OSes, and languages.
- Get visits per a period of time like a month of a year of an item or model.
- Supports multiple data engines: Redis or database (any SQL engine that Eloquent supports). 


## Install
To get started with Laravel Visits, use Composer to add the package to your project's dependencies:
```bash
composer require awssat/laravel-visits
```
#### Requirement
- Laravel 5.5+
- PHP 7.2+
- Data engines options (can be configured from config/visits.php): 
  - Redis: make sure that Redis is configured and ready. (see [Laravel Redis Configuration](https://laravel.com/docs/5.6/redis#configuration))
  - Database: publish migration file: `php artisan vendor:publish --provider="Awssat\Visits\VisitsServiceProvider" --tag="migrations"` then migrate.



#### Config
To adjust the package to your needs, you can publish the config file to your project's config folder using:

```bash
php artisan vendor:publish --provider="awssat\Visits\VisitsServiceProvider"
```

> **Note** : Redis Database Name

- By default `laravel-visits` doesn't use the default laravel redis configuration (see [issue #5](https://github.com/awssat/laravel-visits/issues/5))

To prevent any data loss add a new connection on `config/database.php`

```php
'laravel-visits' => [
    'host' => env('REDIS_HOST', '127.0.0.1'),
    'password' => env('REDIS_PASSWORD', null),
    'port' => env('REDIS_PORT', 6379),
    'database' => 3, // anything from 1 to 15, except 0 (or what is set in default)
],
```

and you can define your redis connection name on `config/visits.php`
```php

'connection' => 'default' // to 'laravel-visits'
```


## Usage

It's simple.
Using `visits` helper as:

```php
visits($model)->{method}()
```
Where:
- **$model**: is any Eloquent model from your project.
- **{method}**: any method that is supported by this library, and they are documented below.

#### Tags
- You can track multiple kinds of visits to a single model using the tags as `visits($model,'tag1')->increment()`

## Increments and Decrements

#### Increment
##### One
```php
visits($post)->increment();
```
##### More than one
```php
visits($post)->increment(10);
```

#### Decrement
##### One
```php
visits($post)->decrement();
```
##### More than one
```php
visits($post)->decrement(10);
```

#### Only increment/decrement once during x seconds (based on visitor's IP)
```php
visits($post)->seconds(30)->increment()
```
> **Note:** this will override default config setting (once each 15 minutes per IP).


#### Force increment/decrement
```php
visits($post)->forceIncrement();
visits($post)->forceDecrement();
```
- This will ignore IP limitation and increment/decrement every visit.


## An item visits

#### All visits of an item
```php
visits($post)->count();
```
> **Note:** $post is a row of a model, i.e. $post = Post::find(22);

#### Item's visits by a period
```php
visits($post)->period('day')->count();
```

## A model class visits

#### All visits of a model type
```php
visits('App\Post')->count();
```

#### Visits of a model type in period
```php
visits('App\Post')->period('day')->count();
```

## Countries of visitors
```php
visits($post)->countries();
```

## Referers of visitors
```php
visits($post)->refs();
```

## Operating Systems of visitors
```php
visits($post)->operatingSystems();
```

## Languages of visitors
```php
visits($post)->languages();
```

## Top or Lowest list per model type

#### Top/Lowest 10
```php
visits('App\Post')->top(10);
```
```php
visits('App\Post')->low(10);
```

#### Uncached list
```php
visits('App\Post')->fresh()->top(10);
```
> **Note:** you can always get uncached list by enabling `alwaysFresh` from package config.

#### By a period of time
```php
visits('App\Post')->period('month')->top(10);
```

## Reset and clear values

#### Clear an item visits
```php
visits($post)->reset();
```

#### Clear an item visits of specific period
```php
visits($post)->period('year')->reset();
```

#### Clear recorded visitors' IPs
```php
visits($post)->reset('ips');
visits($post)->reset('ips','127.0.0.1');
```


### Periods options

- minute
- hour
- xhours [1hours ... to 12hours]
- day
- week
- month
- year
- quarter
- decade
- century

you can also make your custom period by adding a carbon marco in `AppServiceProvider`:

```php
Carbon::macro('endOf...', function () {
    //
});
```

#### Other
```php
//clear all visits of the given model and its items
visits('App\Post')->reset();
//clear all cache of the top/lowest list
visits('App\Post')->reset('lists');
//clear visits from all items of the given model in a period
visits('App\Post')->period('year')->reset();
//...?
visits('App\Post')->reset('factory');
//increment/decrement methods offer ignore parameter to stop recording any items of ('country', 'refer', 'periods', 'operatingSystem', 'language')
visits('App\Post')->increment(1, false, ['country']);
```

## Integration with Eloquent

You can add a `visits` method to your model class:

```php
public function visits()
{
    return visits($this);
}
```

Then you can use it as:

```php
$post = Post::find(1);
$post->visits()->increment();
$post->visits()->count();
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Credits

- [Bader][link-author]
- [All Contributors][link-contributors]

## Todo
- An export command to save visits of any periods to a table on the database.

## Contributors

### Code Contributors

This project exists thanks to all the people who contribute. [[Contribute](CONTRIBUTING.md)].
<a href="https://github.com/awssat/laravel-visits/graphs/contributors"><img src="https://opencollective.com/laravel-visits/contributors.svg?width=890&button=false" /></a>

### Financial Contributors

Become a financial contributor and help us sustain our community. [[Contribute](https://opencollective.com/laravel-visits/contribute)]

#### Individuals

<a href="https://opencollective.com/laravel-visits"><img src="https://opencollective.com/laravel-visits/individuals.svg?width=890"></a>

#### Organizations

Support this project with your organization.
Your logo will show up here with a link to your website.
[[Contribute](https://opencollective.com/laravel-visits/contribute)]

<a href="https://opencollective.com/laravel-visits/organization/0/website"><img src="https://opencollective.com/laravel-visits/organization/0/avatar.svg"></a>
<a href="https://opencollective.com/laravel-visits/organization/1/website"><img src="https://opencollective.com/laravel-visits/organization/1/avatar.svg"></a>
<a href="https://opencollective.com/laravel-visits/organization/2/website"><img src="https://opencollective.com/laravel-visits/organization/2/avatar.svg"></a>
<a href="https://opencollective.com/laravel-visits/organization/3/website"><img src="https://opencollective.com/laravel-visits/organization/3/avatar.svg"></a>
<a href="https://opencollective.com/laravel-visits/organization/4/website"><img src="https://opencollective.com/laravel-visits/organization/4/avatar.svg"></a>
<a href="https://opencollective.com/laravel-visits/organization/5/website"><img src="https://opencollective.com/laravel-visits/organization/5/avatar.svg"></a>
<a href="https://opencollective.com/laravel-visits/organization/6/website"><img src="https://opencollective.com/laravel-visits/organization/6/avatar.svg"></a>
<a href="https://opencollective.com/laravel-visits/organization/7/website"><img src="https://opencollective.com/laravel-visits/organization/7/avatar.svg"></a>
<a href="https://opencollective.com/laravel-visits/organization/8/website"><img src="https://opencollective.com/laravel-visits/organization/8/avatar.svg"></a>
<a href="https://opencollective.com/laravel-visits/organization/9/website"><img src="https://opencollective.com/laravel-visits/organization/9/avatar.svg"></a>

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/awssat/laravel-visits.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://travis-ci.org/awssat/laravel-visits.svg?branch=master
[ico-code-quality]: https://scrutinizer-ci.com/g/awssat/laravel-visits/badges/quality-score.png?b=master
[ico-downloads]: https://img.shields.io/packagist/dt/awssat/laravel-visits.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/awssat/laravel-visits
[link-travis]: https://travis-ci.org/awssat/laravel-visits
[link-scrutinizer]: https://scrutinizer-ci.com/g/awssat/laravel-visits/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/awssat/laravel-visits
[link-downloads]: https://packagist.org/packages/awssat/laravel-visits
[link-author]: https://github.com/if4lcon
[link-contributors]: ../../contributors
