# Laravel Visits

![aravel-visits](https://i.imgur.com/xHAzl0G.png)

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


> Please support our work here with donation so we can contuine improve this package once we raise fund we will contuine work on this package

Laravel Visits is a counter that can be attached to any model to track its visits with useful features like IP-protection and lists caching.

## Install

To get started with Laravel Visits, use Composer to add the package to your project's dependencies (or read more about installlation on [Installation](docs/3_installation.md) page):

```bash
composer require awssat/laravel-visits
```

## Docs & How-to use & configure

-   [Introduction](docs/1_introduction.md)
-   [Requirements](docs/2_requirements.md)
-   [Installation](docs/3_installation.md)
-   [Quick start](docs/4_quick-start.md)
-   [Increments and decrements](docs/5_increments-and-decrements.md)
-   [Retrieve visits and stats](docs/6_retrieve-visits-and-stats.md)
-   [Visits lists](docs/7_visits-lists.md)
-   [Clear and reset values](docs/8_clear-and-reset-values.md)

## Configuration

You can publish the configuration file using the following command:

```bash
php artisan vendor:publish --provider="Awssat\Visits\VisitsServiceProvider" --tag="config"
```

This will create a `config/visits.php` file in your application. In this file, you can configure the behavior of the package.

### `global_ignore`

The `global_ignore` option allows you to prevent the recording of certain types of data. By default, no data is ignored. You can choose to ignore any of the following: `'country'`, `'refer'`, `'periods'`, `'operatingSystem'`, `'language'`.

For example, to ignore country and language tracking, you would set the option like this:

```php
'global_ignore' => ['country', 'language'],
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Credits

-   [Bader][link-author]
-   [All Contributors][link-contributors]

## Todo

-   An export command to save visits of any periods to a table on the database.

## Contributors

### Code Contributors

This project exists thanks to all the people who contribute. [[Contribute](CONTRIBUTING.md)].
<a href="https://github.com/awssat/laravel-visits/graphs/contributors"><img src="https://opencollective.com/laravel-visits/contributors.svg?width=890&button=false" /></a>

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
