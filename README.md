![](https://banners.beyondco.de/Parse%20Contribuyente.png?theme=light&packageManager=composer+require&packageName=trienlace%2Fparse-contribuyente&pattern=circuitBoard&style=style_1&description=A+parser+for+the+SENIAT+Contribuyente+search+page&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Fwww.php.net%2Fimages%2Flogos%2Fnew-php-logo.svg)

# A Parser for the SENIAT Contribuyente search page

[![Latest Version on Packagist](https://img.shields.io/packagist/v/trienlace/parse-contribuyente.svg?style=flat-square)](https://packagist.org/packages/trienlace/parse-contribuyente)
![Tests](https://github.com/trienlace/parse-contribuyente/actions/workflows/run-tests.yml/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/trienlace/parse-contribuyente.svg?style=flat-square)](https://packagist.org/packages/trienlace/parse-contribuyente)

## Installation

You can install the package via composer:

```bash
composer require trienlace/parse-contribuyente
```

The package will automatically register itself.

### `ParseContribuyente`

This validation rule will pass if the RIF value passed in the request is valid.

```php
// in a Controller, Job or View

use Trienlace\ParseContribuyente\ParseContribuyente;

$body = '<HTML>...</HTML>'

$parser = new ParseContribuyente($body);

$contribuyente = $parser->toArray()
```

### Testing

``` bash
vendor/bin/pest
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

### Security

If you discover any security related issues, please email seguridad@trienlace.com instead of using the issue tracker.

## Credits

- [Trienlace, C.A.](https://github.com/trienlace)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.