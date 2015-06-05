# Braintree PHP Client Library

The Braintree PHP library provides integration access to the Braintree Gateway.

## Dependencies

PHP version >= 5.4.0 required.

The following PHP extensions are required:

* curl
* dom
* hash
* openssl
* SimpleXML
* xmlwriter

## Installation
```php
composer require braintree/braintree_php
```

## Quick Start Example

```php
<?php

use Braintree\Configuration;
use Braintree\Transaction;

Configuration::environment('sandbox');
Configuration::merchantId('your_merchant_id');
Configuration::publicKey('your_public_key');
Configuration::privateKey('your_private_key');

$result = Transaction::sale(array(
    'amount' => '1000.00',
    'creditCard' => array(
        'number' => '5105105105105100',
        'expirationDate' => '05/12'
    )
));

if ($result->success) {
    print_r("success!: " . $result->transaction->id);
} else if ($result->transaction) {
    print_r("Error processing transaction:");
    print_r("\n  code: " . $result->transaction->processorResponseCode);
    print_r("\n  text: " . $result->transaction->processorResponseText);
} else {
    print_r("Validation errors: \n");
    print_r($result->errors->deepAll());
}


```

## Legacy PHP Support

Version [2.40.0](https://github.com/braintree/braintree_php/releases/tag/2.40.0) is compatible with PHP 5.2 and 5.3. You can find it on our releases page.

## Documentation

 * [Official documentation](https://developers.braintreepayments.com/php/sdk/server/overview)

## Testing

Tests are written in PHPunit (installed by composer). Unit tests should run on
any system meeting the base requirements:

    phpunit tests/unit/

Please note that the integration tests require access to services internal to 
Braintree, and so will not run in your test environment.

## Open Source Attribution

A list of open source projects that help power Braintree can be found [here](https://www.braintreepayments.com/developers/open-source).

## License

See the LICENSE file.
