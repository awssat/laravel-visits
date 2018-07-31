# Laravel Visits

![aravel-visits](https://i.imgur.com/xHAzl0G.png)


[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


## Introduction
Laravel Visits is a counter that can be attached to any model to track its visits with useful features like IP-protection and lists caching


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
  * [Change log](#change-log)
  * [Contributing](#contributing)
  * [Credits](#credits)
  * [License](#license)



## Features
- A model item can has many type of recorded visits (using tags).
- It's not limitd to one type of Model (like some packages that allow only User model).
- Record per visitors and not by vistis using IP detecting, so even with refresh visit won't duplicate (can be changed from config). 
- Get Top/Lowest visits per a model.
- Get most visited countries ...
- Get visits per a period of time like a month of a year of an item or model.



## Install

Via Composer
``` bash
composer require awssat/laravel-visits
```
#### Requirement
- This package rely on heavly on Redis. To use it, make sure that Redis is configured and ready. (see [Laravel Redis Configuration](https://laravel.com/docs/5.6/redis#configuration))


#### Before Laravel 5.5
In Laravel 5.4. you'll manually need to register the `awssat\Visits\VisitsServiceProvider::class` service provider in `config/app.php`.

#### Config
To adjust the library, you can publish the config file to your project using:
```
php artisan vendor:publish --provider="awssat\Visits\VisitsServiceProvider"
```

### Upgrade to 1.4.0 from 1.3.*

- Prfiex updated from `bareq` to `visits` if you missing your data try to revert prefex value to `bareq`
  


### Note : Redis Database Name

- By default `laravel-visits` doesn't use the default laravel redis configuration see issue (see [issue #5](https://github.com/awssat/laravel-visits/issues/5))

To prvent your data loss add a new conection on `config/database.php`

``` php

        'laravel-visits' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 3, // anything from 1 to 15, except 0 (or what is set in default)
        ],

```

and you can define your redis connection name on `config/visits.php`

``` php

'connection' => 'default'

```


## Usage

It's simple. Using `visits` helper as: 
```
visits($model)->{method}()
```
Where:
- **$model**: is any Eloquent model from your project.
- **{method}**: any method that is supported by this library, and they are documented below.

#### Tags
- You can track multiple kinds of visits to a single model using the tags as `visits($model, 'tag1')->increment()`


## Increments and Decrements

#### Increment
##### One
``` php
visits($post)->increment();
```
##### More than one 
``` php
visits($post)->increment(10);
```

#### Decrement
##### One
``` php
visits($post)->decrement();
```
##### More than one 
``` php
visits($post)->decrement(10);
```

#### Only increment/decrement once during x seconds (based on visitor's IP)
``` php
visits($post)->seconds(30)->increment()
```
- Note: this will override default config setting (once each 15 minutes per IP).


#### Force increment/decrement
``` php
visits($post)->forceIncrement();
visits($post)->forceDecrement();
```
- This will ignore IP limitation and increment/decrement every visit.



## An item visits

#### All visits of an item
``` php
visits($post)->count();
```
- Note: $post is a row of a model, i.e. $post = Post::find(22)


#### Item's visits by a period  
``` php
visits($post)->period('day')->count();
```

## A model class visits

#### All visits of a model type
``` php
visits('App\Post')->count()
```

#### Visits of a model type in period
``` php
visits('App\Post')->period('day')->count()
```

## Countries of visitors
``` php
visits($post)->countries()
```

## Referers of visitors
``` php
visits($post)->refs()
```


## Top or Lowest list per model type

#### Top/Lowest 10
``` php
visits('App\Post')->top(10)
```
``` php
visits('App\Post')->low(10)
```

#### Uncached list
``` php
visits('App\Post')->fresh()->top(10)
```
- Note: you can always get uncached list by enabling `alwaysFresh` from package config.

#### By a period of time
``` php
visits('App\Post')->period('month')->top(10)
```


## Reset and clear values

#### Clear an item visits
``` php
visits($post)->reset();
```

#### Clear an item visits of specific period
``` php
visits($post)->period('year')->reset()
```

#### Clear recorded visitors' IPs
``` php
visits($post)->reset('ips');
visits($post)->reset('ips', '127.0.0.1');
```

#### Other
``` php
//clear all visits of the given model and its items
visits('App\Post')->reset()
//clear all cache of the top/lowest list
visits('App\Post')->reset('lists')
//clear visits from all items of the given model in a period
visits('App\Post')->period('year')->reset() 
//...?
visits('App\Post')->reset('factory')
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


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Credits

- [Bader][link-author]
- [All Contributors][link-contributors]

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
