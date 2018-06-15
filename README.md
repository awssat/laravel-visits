# Bareq

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Laravel Redis visits counter for Eloquent models 

**Note:** Tested with Laravel 5.5 . 

## Install

Via Composer

``` bash
$ composer require if4lcon/bareq
```

The package will automatically register itself in Laravel 5.5. In Laravel 5.4. you'll manually need to register the `if4lcon\Bareq\BareqServiceProvider::class` service provider in `config/app.php`.

You can publish the config file with:

```
php artisan vendor:publish --provider="if4lcon\Bareq\BareqServiceProvider"
```

## Usage

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

[ico-version]: https://img.shields.io/packagist/v/if4lcon/Bareq.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://travis-ci.org/awssat/Bareq.svg?branch=master
[ico-code-quality]: https://scrutinizer-ci.com/g/if4lcon/Bareq/badges/quality-score.png?b=master
[ico-downloads]: https://img.shields.io/packagist/dt/if4lcon/Bareq.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/if4lcon/Bareq
[link-travis]: https://travis-ci.org/awssat/Bareq
[link-scrutinizer]: https://scrutinizer-ci.com/g/if4lcon/Bareq/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/if4lcon/Bareq
[link-downloads]: https://packagist.org/packages/if4lcon/Bareq
[link-author]: https://github.com/if4lcon
[link-contributors]: ../../contributors
