# Braintree PHP Client Library

The Braintree PHP library provides integration access to the Braintree Gateway. Updated original to support Namespaces and PSR-4 load.

## Dependencies

PHP version >= 5.3 required.

The following PHP extensions are required:

* curl
* dom
* hash
* openssl
* SimpleXML
* xmlwriter

## Composer install

```"braintree/braintree_php" : "dev-master"```

## Quick Start Example

```php
<?php

require_once '/braintree/folder/src/autoload.php';

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

## Documentation

 * [Official documentation](https://developers.braintreepayments.com/php/sdk/server/overview)

## Open Source Attribution

A list of open source projects that help power Braintree can be found [here](https://www.braintreepayments.com/developers/open-source).

## License

See the LICENSE file.
