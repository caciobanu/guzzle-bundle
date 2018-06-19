# Guzzle Symfony Bundle

This is a [Symfony](https://github.com/symfony/symfony) bundle that integrates [Guzzle](https://github.com/guzzle/guzzle) for easier use.

[![Build Status](https://travis-ci.org/caciobanu/guzzle-bundle.svg?branch=master)](https://travis-ci.org/caciobanu/guzzle-bundle)
[![Code Coverage](https://scrutinizer-ci.com/g/caciobanu/guzzle-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/caciobanu/guzzle-bundle/?branch=master)

## Installation

You can use [Composer](https://getcomposer.org/) to install the extension to your project:

```bash
composer require caciobanu/guzzle-bundle
```

Then create a minimal config file `caciobanu_guzzle.yml` in `config/packages/`:

```yml
caciobanu_guzzle:
    clients:
        google:
            base_uri: 'https://google.com'
```

A complete configuration looks like:

```yml
caciobanu_guzzle:
    clients:
        google:
            client_class: 'Your\Client'    # You must extend 'GuzzleHttp\Client' which is the default value.
            base_uri: 'https://google.com'
            logging: true                  # Enable logging. Default value: false.
            options:                       # See http://docs.guzzlephp.org/en/stable/request-options.html for all available options.
                timeout: 30
                headers:
                    'User-Agent': 'Test Agent'
```

## Usage

Using services in controller:

```php
/** @var \GuzzleHttp\Client $client */
$client   = $this->get('caciobanu_guzzle.client.google');
$response = $client->get('/');
```

## Adding Guzzle middleware

Adding a Guzzle middleware is a two step process:

1. Create a new class:
```php
<?php

namespace App\Guzzle\Middleware;

use Caciobanu\Symfony\GuzzleBundle\Middleware\BeforeRequestMiddlewareInterface;
use Psr\Http\Message\RequestInterface;

class MyMiddleware implements BeforeRequestMiddlewareInterface
{
    public function __invoke(RequestInterface $request): RequestInterface
    {
        // Do something with the request
        return $request;
    }
}
```

2. Create a Symfony service like so:
```
# config/services.yaml
services:
    App\Guzzle\Middleware\MyMiddleware:
        tags:
            - { name: 'caciobanu_guzzle.middleware', client: 'google' }
```

There are three middleware interfaces that can be implemented:
- Caciobanu\Symfony\GuzzleBundle\Middleware\BeforeRequestMiddlewareInterface - marks middleware to be called before sending the request
- Caciobanu\Symfony\GuzzleBundle\Middleware\AfterResponseMiddlewareInterface - marks middleware to be called after the response is received
- Caciobanu\Symfony\GuzzleBundle\Middleware\OnErrorMiddlewareInterface - marks middleware to be called when an errors occurs

## Credits

This library is developed by [Catalin Ciobanu](https://github.com/caciobanu).

## License

[![license](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)
