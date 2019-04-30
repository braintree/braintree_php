# Braintree PHP library

The Braintree PHP library provides integration access to the Braintree Gateway.

## Dependencies

PHP version >= 7.2 is required.

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

// Instantiate a Braintree Gateway either like this:
$gateway = new Braintree_Gateway([
    'environment' => 'sandbox',
    'merchantId' => 'your_merchant_id',
    'publicKey' => 'your_public_key',
    'privateKey' => 'your_private_key'
]);

// or like this:
$config = new Braintree_Configuration([
    'environment' => 'sandbox',
    'merchantId' => 'your_merchant_id',
    'publicKey' => 'your_public_key',
    'privateKey' => 'your_private_key'
]);
$gateway = new Braintree\Gateway($config)

// Then, create a transaction:
$result = $gateway->transaction()->sale([
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
$gateway = new Braintree\Gateway([
    'environment' => 'sandbox',
    'merchantId' => 'your_merchant_id',
    'publicKey' => 'your_public_key',
    'privateKey' => 'your_private_key'
]);

// or

$config = new Braintree\Configuration([
    'environment' => 'sandbox',
    'merchantId' => 'your_merchant_id',
    'publicKey' => 'your_public_key',
    'privateKey' => 'your_private_key'
]);
$gateway = new Braintree\Gateway($config)
```

## TLS 1.2 required
> **The Payment Card Industry (PCI) Council has [mandated](https://blog.pcisecuritystandards.org/migrating-from-ssl-and-early-tls) that early versions of TLS be retired from service.  All organizations that handle credit card information are required to comply with this standard. As part of this obligation, Braintree has updated its services to require TLS 1.2 for all HTTPS connections. Braintrees require HTTP/1.1 for all connections. Please see our [technical documentation](https://github.com/paypal/tls-update) for more information.**

## Google App Engine Support

When using Google App Engine include the curl extention in your `php.ini` file (see [#190](https://github.com/braintree/braintree_php/issues/190) for more information):

```ini
extension = "curl.so"
```

and turn off accepting gzip responses:

```php
$gateway = new Braintree\Gateway([
    'environment' => 'sandbox',
    // ...
    'acceptGzipEncoding' => false,
]);
```

## Legacy PHP Support

Version [3.39.0](https://github.com/braintree/braintree_php/releases/tag/3.39.0) is compatible with PHP 5.4. You can find it on our releases page.

## Documentation

 * [Official documentation](https://developers.braintreepayments.com/php/sdk/server/overview)

## Developing (Docker)

The `Makefile` and `Dockerfile` will build an image containing the dependencies and drop you to a terminal where you can run tests.

```
make
```

## Testing

The unit specs can be run by anyone on any system, but the integration specs are meant to be run against a local development server of our gateway code. These integration specs are not meant for public consumption and will likely fail if run on your system. To run unit tests use rake: `rake test:unit`.

## License

See the LICENSE file.
