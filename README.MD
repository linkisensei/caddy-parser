# Caddy Parser

A PHP library for parsing and eventually writing [Caddyfile](https://caddyserver.com/docs/caddyfile) configuration files.

> ⚠️ This library is under active development and **not ready for production use**.  
> It was started as part of a future project, so there is no rush to finish it.  
> The test coverage is **incomplete** and does **not guarantee correctness**.

## Features

- Parse Caddyfile configurations into structured PHP objects
- Export Caddyfile structures back to plain text (Work in progress)
- Modular design with extensible elements and block types (I wish)

## Installation

Install via Composer:

```bash
composer require linkisensei/caddy-parser:dev-main \
    --repository='{"type":"vcs", "url":"https://github.com/linkisensei/caddy-parser"}'
```

## Usage

```php
use CaddyParser\CaddyConfig\Parser\CaddyfileParser;

/** @var CaddyParser\CaddyConfig\Caddyfile */
$caddyfile = CaddyfileParser::parseFile('/path/to/Caddyfile');

// You can access elements like global options, site blocks, etc.
$global = $caddyfile->getGlobalOptions();
$sites = $caddyfile->getSiteBlocks();
```

## Roadmap

- [x] Lexer and parser for Caddyfile syntax
- [x] Support for global options, snippets, site blocks, and directives
- [x] Export to Caddyfile (in progress)
- [ ] More complete test coverage
- [ ] Real-world production validation

## Notes

- Writing/exporting the Caddyfile (`toCaddyfile()` methods) is **under development** and **has not been tested yet**.
- You are welcome to explore, fork, or contribute—but please note that the API may change.

## License

MIT