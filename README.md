![Tikamoon](https://www.tikamoon.online/template-logo_tikanoir.svg)


## Overview

This plugin enables using Klarna payments in Sylius based stores.

## Support

TODO

## Demo

TODO

## Installation
```bash
$ composer require tikamoon/klarna-plugin
```
    
Add plugin dependencies to your AppKernel.php file:
```php
public function registerBundles()
{
    return array_merge(parent::registerBundles(), [
        ...
        
        new \Tikamoon\KlarnaPlugin\TikamoonKlarnaPlugin(),
    ]);
}
```

Add Path to Twig.yaml
```yaml
    '%kernel.project_dir%/vendor/tikamoon/klarna-plugin/templates' : klarna
```

## Usage

Go to the payment methods in your admin panel. Now you should be able to add new payment method for Klarna gateway.


## Contribution

Learn more about our contribution workflow on http://docs.sylius.org/en/latest/contributing/.
