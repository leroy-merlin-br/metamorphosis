# Metamorphosis

> Easy and flexible Kafka Library for Laravel and PHP 7.

![Metamorphosis](./docs/logo.png)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/leroy-merlin-br/metamorphosis.svg?style=flat-square)](https://packagist.org/packages/leroy-merlin-br/metamorphosis)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/leroy-merlin-br/metamorphosis.svg?style=flat-square)](https://packagist.org/packages/leroy-merlin-br/metamorphosis)
[![Build Status](https://travis-ci.org/leroy-merlin-br/metamorphosis.svg?branch=master)](https://travis-ci.org/leroy-merlin-br/metamorphosis)
[![Coverage Status](https://coveralls.io/repos/github/leroy-merlin-br/metamorphosis/badge.svg?branch=master)](https://coveralls.io/github/leroy-merlin-br/metamorphosis?branch=master)
[![StyleCI](https://styleci.io/repos/143462354/shield?branch=master)](https://styleci.io/repos/143462354)


- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Usage Guide](docs/quick-usage.md)
- [Advanced Usage Guide](docs/advanced.md)
- [Contributing](docs/CONTRIBUTING.md)
- [License](#license)


<a name="introduction"></a>
## Introduction

Metamorphosis provides a simple, straight-forward implementation for working with Kafka inside Laravel applications.

<a name="requirements"></a>
## Requirements

- PHP >= 7.1
- [Kafka Driver](https://github.com/edenhill/librdkafka)
- [Kafka PHP Extension](https://github.com/arnaud-lb/php-rdkafka)

<a name="installation"></a>
## Installation

**Important:** *Make sure that you already have installed the OS driver for kafka, and the kafka php extension.*


You can install the library via Composer:

```
$ composer require leroy-merlin-br/metamorphosis
```

And publish the config file with:

```
$ php artisan vendor:publish --provider="Metamorphosis\MetamorphosisServiceProvider"
```

For usage instructions, please refer to our [Quick Usage Guide](docs/quick-usage.md).

<a name="license"></a>
## License

Metamorphosis is free software distributed under the terms of the [MIT license](http://opensource.org/licenses/MIT)

<a name="additional_information"></a>
## Additional information

Metamorphosis was proudly built by the [Leroy Merlin Brazil](https://github.com/leroy-merlin-br) team. [See all the contributors](https://github.com/leroy-merlin-br/metamorphosis/graphs/contributors).
