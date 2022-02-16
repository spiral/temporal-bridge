# Temporal integration package for Spiral Framework

[![PHP](https://img.shields.io/packagist/php-v/spiral/temporal-bridge.svg?style=flat-square)](https://packagist.org/packages/spiral/temporal-bridge)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/spiral/temporal-bridge.svg?style=flat-square)](https://packagist.org/packages/spiral/temporal-bridge)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spiral/temporal-bridge/run-tests?label=tests&style=flat-square)](https://github.com/spiral/temporal-bridge/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spiral/temporal-bridge.svg?style=flat-square)](https://packagist.org/packages/spiral/temporal-bridge)

## Requirements

Make sure that your server is configured with following PHP version and extensions:

- PHP 8.0+
- Spiral framework 2.9+
 
## Installation

You can install the package via composer:

```bash
composer require spiral/temporal-bridge
```

After package install you need to register bootloader from the package.

```php
protected const LOAD = [
    // ...
    \Spiral\TemporalBridge\Bootloader\TemporalBridgeBootloader::class,
];
```

> Note: if you are using [`spiral-packages/discoverer`](https://github.com/spiral-packages/discoverer), 
> you don't need to register bootloader by yourself.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [butschster](https://github.com/spiral)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
