# Bareq

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Laravel Redis visits counter for Eloquent models 

**Note:** Tested with Laravel 5.5 . 

## Install

Via Composer

``` bash
$ composer require phpfalcon/Bareq
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

## Testing

Copy `` visitsTest.php `` to your Laravel tests folder and make sure to change the tested model with yours

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Credits

- [Bader][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/phpfalcon/Bareq.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/phpfalcon/Bareq/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/phpfalcon/Bareq.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/phpfalcon/Bareq.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/phpfalcon/Bareq.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/phpfalcon/Bareq
[link-travis]: https://travis-ci.org/phpfalcon/Bareq
[link-scrutinizer]: https://scrutinizer-ci.com/g/phpfalcon/Bareq/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/phpfalcon/Bareq
[link-downloads]: https://packagist.org/packages/phpfalcon/Bareq
[link-author]: https://github.com/phpfalcon
[link-contributors]: ../../contributors
