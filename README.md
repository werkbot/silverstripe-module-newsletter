# SilverStripe Newsletter Module
[![Latest Stable Version](http://poser.pugx.org/werkbot/newsletter-module/v)](https://packagist.org/packages/werkbot/newsletter-module) [![Total Downloads](http://poser.pugx.org/werkbot/newsletter-module/downloads)](https://packagist.org/packages/werkbot/newsletter-module) [![Latest Unstable Version](http://poser.pugx.org/werkbot/newsletter-module/v/unstable)](https://packagist.org/packages/werkbot/newsletter-module) [![License](http://poser.pugx.org/werkbot/newsletter-module/license)](https://packagist.org/packages/werkbot/newsletter-module) [![PHP Version Require](http://poser.pugx.org/werkbot/newsletter-module/require/php)](https://packagist.org/packages/werkbot/newsletter-module)

Silverstripe module to capture user emails. This also integrates with third-party mail subscription services:
- Campaign Monitor
- Mailchimp
- Constant Contact
- Active Campaign
- Redtail CRM

## Installation
```
composer require werkbot/newsletter-module
```

#### Requirements
- Silverstripe ^4.0

## Setup
- You will need to run `/dev/build`
- Place `$NewsletterForm` somewhere in your template.
- To use the default styles, include this in your main sass file:

`@import 'newsletter-module/sass/newsletter';`

> *Depending on your setup, you may need to include `vendor/werkbot` in your build path. For example, we include: `includePaths: [ 'vendor/werkbot' ]` for sass*


## Usage
* [Usage documentation](docs/en/README.md)
