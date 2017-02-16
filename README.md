# Braintree PHP SDK

The Braintree PHP SDK provides integration access to the Braintree Gateway.

## Please Note
> **The Payment Card Industry (PCI) Council has [mandated](http://blog.pcisecuritystandards.org/migrating-from-ssl-and-early-tls) that early versions of TLS be retired from service.  All organizations that handle credit card information are required to comply with this standard. As part of this obligation, Braintree is updating its services to require TLS 1.2 for all HTTPS connections. Braintree will also require HTTP/1.1 for all connections. Please see our [technical documentation](https://github.com/paypal/tls-update) for more information.**

## Dependencies

PHP version >= 5.4.0 is required.

The following PHP extensions are required:

* curl
* dom
* hash
* openssl
* xmlwriter

## Quick Start Example

```php
<?php

require_once 'PATH_TO_BRAINTREE/lib/Braintree.php';

Braintree_Configuration::environment('sandbox');
Braintree_Configuration::merchantId('your_merchant_id');
Braintree_Configuration::publicKey('your_public_key');
Braintree_Configuration::privateKey('your_private_key');

$result = Braintree_Transaction::sale([
    'amount' => '1000.00',
    'paymentMethodNonce' => 'nonceFromTheClient',
    'options' => [ 'submitForSettlement' => true ]
]);

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

Both PSR-0 and PSR-4 namespacing are supported. If you are using composer with `--classmap-authoritative` or
`--optimize-autoloader` enabled, you'll have to reference classes using PSR-4 namespacing:

```php
Braintree\Configuration::environment('sandbox');
Braintree\Configuration::merchantId('your_merchant_id');
Braintree\Configuration::publicKey('your_public_key');
Braintree\Configuration::privateKey('your_private_key');
```

## HHVM Support

The Braintree PHP SDK will run on HHVM >= 3.4.2.

## Google App Engine

If you use Google App Engine, you'll have to turn off accepting gzip responses.

```php
Braintree\Configuration::acceptGzipEncoding(false);
```

## Legacy PHP Support

Version [2.40.0](https://github.com/braintree/braintree_php/releases/tag/2.40.0) is compatible with PHP 5.2 and 5.3. You can find it on our releases page.

## Documentation

 * [Official documentation](https://developers.braintreepayments.com/php/sdk/server/overview)

## Testing

The unit specs can be run by anyone on any system, but the integration specs are meant to be run against a local development server of our gateway code. These integration specs are not meant for public consumption and will likely fail if run on your system. To run unit tests use rake: `rake test:unit`.

The benefit of the `rake` tasks is that testing covers default `hhvm` and `php` interpreters. However, if you want to run tests manually simply use the following command:
```
phpunit tests/unit/
```

## License

See the LICENSE file.
