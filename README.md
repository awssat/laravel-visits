# Laravel Visits

![aravel-visits](https://i.imgur.com/xHAzl0G.png)


[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


## Introduction
Laravel Visits is a counter that can be attached to any model to track its visits with useful features like IP-protection and lists caching


## Install

Via Composer
``` bash
composer require awssat/laravel-visits
```

### Before Laravel 5.5
In Laravel 5.4. you'll manually need to register the `if4lcon\Bareq\BareqServiceProvider::class` service provider in `config/app.php`.

### Config
To adjust the library, you can publish the config file to your project using:
```
php artisan vendor:publish --provider="if4lcon\Bareq\BareqServiceProvider"
```

## Usage

It's simple. Using `visits` helper as: 
```
visits($model)->{method}()
```
Where:
- **$model**: is any Eloquent model from your project.
- **{method}**: any method that is supported by this library, and they are documented below.

### Tags
- You can track multiple kinds of visits to a single model using the tags as `visits($model, 'tag1')->increment()`


## Increments :

#### Normal increment
``` php
visits($post)->increment();
```

#### Increment number of times
``` php
visits($post)->increment(10);
```

#### Set IP expiration time in seconds
``` php
visits($post)->seconds(30)->increment()
```

#### Decrement 
``` php
visits($post)->decrement();
```

#### Decrement number of times
``` php
visits($post)->decrement(10);
```

#### Force increment / decrement 
``` php
visits($post)->forceIncrement();
visits($post)->forceDecrement();
```
## Top/Low list :

#### Top 10
``` php
visits('App\Post')->top(10)
```

#### Lowest 10
``` php
visits('App\Post')->low(10)
```

#### Get fresh list
``` php
visits('App\Post')->fresh()->top(10)
```

#### Get top/low of periods
``` php
visits('App\Post')->period('month')->top(10)
```

## Counts :

#### Subject visits
``` php
visits($post)->count();
```

#### Subject visits period
``` php
visits($post)->period('day')->count();
```

#### All subjects
``` php
visits('App\Post')->count()
```

#### All subjects period
``` php
visits('App\Post')->period('day')->count()
```

## Countries :
``` php
visits($post)->countries()
```

## Referer :
``` php
visits($post)->refs()
```


## Resets :

#### Subject visits
``` php
visits($post)->reset();
```

#### Subject visits period
``` php
visits($post)->period('year')->reset()
```

#### Subject recorded ips
``` php
visits($post)->reset('ips');
visits($post)->reset('ips', '127.0.0.1');
```

#### Reset factory
``` php
visits('App\Post')->reset('factory')
```

#### Other :
``` php
 * visits('App\Post')->reset()
 * visits('App\Post')->reset('lists')
 * visits('App\Post')->period('year')->reset() 
```

## Integration with Eloquent

add ``visits`` method to your model :

```php
    public function visits()
    {
        return visits($this);
    }
```

and you can access it by calling :

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
