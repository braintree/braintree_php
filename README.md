# Braintree PHP Client Library

The Braintree PHP library provides integration access to the Braintree Gateway.

## Dependencies

PHP version >= 5.2.1 required.

The following PHP extensions are required:

* curl
* dom
* hash
* openssl
* SimpleXML
* xmlwriter

## Quick Start Example

    <?php

    require_once 'PATH_TO_BRAINTREE/lib/Braintree.php';

    Braintree_Configuration::environment('sandbox');
    Braintree_Configuration::merchantId('your_merchant_id');
    Braintree_Configuration::publicKey('your_public_key');
    Braintree_Configuration::privateKey('your_private_key');

    $result = Braintree_Transaction::sale(array(
        'amount' => '100.00',
        'creditCard' => array(
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
        )
    ));

    if ($result->success) {
        if ($result->transaction->status == Braintree_Transaction::AUTHORIZED) {
            print_r("success!: " . $result->transaction->id);
        } else {
            print_r("Error processing transaction:");
            print_r("\n  code: " . $result->transaction->processorResponseCode);
            print_r("\n  text: " . $result->transaction->processorResponseText);
        }
    } else {
        print_r("Validation errors: \n");
        print_r($result->errors->deepAll());

    }

    ?>

## License

See the LICENSE file.

