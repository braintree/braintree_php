# Braintree PHP Client Library

The Braintree PHP library provides integration access to the Braintree Gateway.

## Dependencies

* Zend Framework

## Quick Start Example

    <?php

    // ensure Zend_Framework is in your load path
    require_once 'PATH_TO_BRAINTREE/lib/Braintree.php';

    // validates and sets config statically
    Braintree_Configuration::environment('sandbox');
    Braintree_Configuration::merchantId('the_merchant_id');
    Braintree_Configuration::publicKey('the_public_key');
    Braintree_Configuration::privateKey('the_private_key');

    $transaction = Braintree_Transaction::saleNoValidate(array(
        'amount' => '100.00',
        'creditCard' => array(
            'number' => '5105105105105100',
            'expirationDate' => '05/12',
        )
    ));

    print 'Transaction ID: ' . $transaction->id;
    print 'Status: ' . $transaction->status;
    ?>

## License

See the LICENSE file.

