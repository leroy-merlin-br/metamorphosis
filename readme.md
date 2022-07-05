# Metamorphosis

> Easy and flexible Kafka Library for Laravel and PHP 7.

![Metamorphosis](./docs/logo.png)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/leroy-merlin-br/metamorphosis.svg?style=flat-square)](https://packagist.org/packages/leroy-merlin-br/metamorphosis)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](#license)
[![Total Downloads](https://img.shields.io/packagist/dt/leroy-merlin-br/metamorphosis.svg?style=flat-square)](https://packagist.org/packages/leroy-merlin-br/metamorphosis)
[![Build Status](https://github.com/leroy-merlin-br/metamorphosis/workflows/Tests/badge.svg)](https://github.com/leroy-merlin-br/metamorphosis/actions?query=workflow%3ATests)
[![Coverage Status](https://app.codacy.com/project/badge/Coverage/68b086fe75294d3e8c21a72addccb1bc)](https://www.codacy.com/gh/leroy-merlin-br/metamorphosis/dashboard?utm_source=github.com&utm_medium=referral&utm_content=leroy-merlin-br/metamorphosis&utm_campaign=Badge_Coverage)

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

Prefer to read in other language?
- [Português](readme.pt.md)

<a name="requirements"></a>
## Requirements

- PHP >= 7.1
- [Kafka Driver](https://github.com/edenhill/librdkafka)
- [Kafka PHP Extension](https://github.com/arnaud-lb/php-rdkafka)

<a name="installation"></a>
## Installation

### 1. Install the Kafka driver

On Mac OSX, install librdkafka with homebrew:

```bash
brew install librdkafka
```

On Debian and Ubuntu, install librdkafka from the Confluent APT repositories,
see instructions [here](https://docs.confluent.io/current/installation/installing_cp/deb-ubuntu.html#get-the-software) and then install librdkafka:

 ```bash
apt install librdkafka-dev
 ```

On RedHat, CentOS, Fedora, install librdkafka from the Confluent YUM repositories,
instructions [here](https://docs.confluent.io/current/installation/installing_cp/rhel-centos.html#get-the-software) and then install librdkafka:

```bash
yum install librdkafka-devel
```

On Windows, reference [librdkafka.redist](https://www.nuget.org/packages/librdkafka.redist/) NuGet package in your Visual Studio project.

### 2. Install the PHP Kafka extension

On Linux, Unix and OS X, you can install extensions using the PHP Extension Community Library ([PECL](https://www.php.net/manual/en/install.pecl.intro.php)):

```bash
pecl install rdkafka
```

then add the following to your .ini file:

```
extension=rdkafka.so
```
> **Important:** When using multiple PHP versions, PECL will install the package for the latest PHP version only. To set a PHP version, download the source code and compile it specifying the target PHP version.

PHP 7.4 example:

```bash
pecl download rdkafka
tar -xvf rdkafka-X.x.x.tgz
cd rdkafka-X.x.x
phpize
./configure --with-php-config=/usr/bin/php-config7.4
make
sudo make install
``` 

then add the extension to your .ini file:

```
extension=rdkafka.so
```
More about [compiling shared PECL extensions](https://www.php.net/manual/en/install.pecl.phpize.php)

On Windows, download the  [rdkafka DLL](https://pecl.php.net/package/rdkafka/),
put the file in your PHP/ext folder and add the extension to your php.ini file:

```
extension=rdkafka.dll
```

More about [PECL on Windows](https://www.php.net/manual/en/install.pecl.windows.php)

### 3. Install Metamorphosis

Install the library via Composer:

```bash
composer require leroy-merlin-br/metamorphosis
```

And publish the config file with:

```bash
php artisan vendor:publish --provider="Metamorphosis\MetamorphosisServiceProvider"
```

For usage instructions, please refer to our [Quick Usage Guide](docs/quick-usage.md).

<a name="license"></a>
## License

Metamorphosis is free software distributed under the terms of the [MIT license](http://opensource.org/licenses/MIT)

<a name="additional_information"></a>
## Additional information

Metamorphosis was proudly built by the [Leroy Merlin Brazil](https://github.com/leroy-merlin-br) team. [See all the contributors](https://github.com/leroy-merlin-br/metamorphosis/graphs/contributors).
