# Braintree PHP Client Library

The Braintree PHP library provides integration access to the Braintree Gateway. Updated original to support Namespaces and PSR-4 load.

## Dependencies

PHP version >= 5.4.0 is required.

The following PHP extensions are required:

* curl
* dom
* hash
* openssl
* xmlwriter

## Composer install

```"eusonlito/braintree_php" : "dev-master"```

## Quick Start Example

```php
<?php

require_once '/braintree/folder/src/autoload.php';

Braintree\Configuration::reset();
Braintree\Configuration::environment('sandbox');
Braintree\Configuration::merchantId('your_merchant_id');
Braintree\Configuration::publicKey('your_public_key');
Braintree\Configuration::privateKey('your_private_key');

$result = Braintree\Transaction::sale(array(
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

?>
```

## HHVM Support

The Braintree PHP library will run on HHVM >= 3.4.2.

## Legacy PHP Support

Version [2.40.0](https://github.com/braintree/braintree_php/releases/tag/2.40.0) is compatible with PHP 5.2 and 5.3. You can find it on our releases page.

## Documentation

 * [Official documentation](https://developers.braintreepayments.com/php/sdk/server/overview)

## Open Source Attribution

A list of open source projects that help power Braintree can be found [here](https://www.braintreepayments.com/developers/open-source).

## License

See the LICENSE file.
