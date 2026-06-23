# Changelog

All notable changes to `parse-contribuyente` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- Drop support for PHP 7.x; now requires PHP 8.2+.
- Replace the deprecated `paquettg/php-html-parser` dependency with a custom,
  dependency-free HTML parser shipped under the
  `Wilsenhc\ParseContribuyente\HtmlParser` namespace.
- Update development dependencies: bump `pestphp/pest` to `^2.0`, move
  `phpstan/phpstan` to `require-dev` and bump to `^1.12`.
- Replace `utf8_encode` with `mb_convert_encoding` in `ParseContribuyente` to
  avoid the `utf8_encode` deprecation on PHP 8.2+.

### Removed
- `paquettg/php-html-parser` composer dependency.