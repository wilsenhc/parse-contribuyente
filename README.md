![](https://banners.beyondco.de/Parse%20Contribuyente.png?theme=light&packageManager=composer+require&packageName=wilsenhc%2Fparse-contribuyente&pattern=circuitBoard&style=style_1&description=A+parser+for+the+SENIAT+Contribuyente+search+page&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Fwww.php.net%2Fimages%2Flogos%2Fnew-php-logo.svg)

# A Parser for the SENIAT Contribuyente search page

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wilsenhc/parse-contribuyente.svg?style=flat-square)](https://packagist.org/packages/wilsenhc/parse-contribuyente)
![Tests](https://github.com/wilsenhc/parse-contribuyente/actions/workflows/run-tests.yml/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/wilsenhc/parse-contribuyente.svg?style=flat-square)](https://packagist.org/packages/wilsenhc/parse-contribuyente)

## Installation

You can install the package via composer:

```bash
composer require wilsenhc/parse-contribuyente
```

The package will automatically register itself.

### `ParseContribuyente`

This validation rule will pass if the RIF value passed in the request is valid.

```php
// in a Controller, Job or View

use Wilsenhc\ParseContribuyente\ParseContribuyente;

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

If you discover any security related issues, please email wilsenforwork@gmail.com instead of using the issue tracker.

## Credits

- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.