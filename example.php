<?php

require_once __DIR__.'/src/autoload.php';

Braintree\Configuration::reset();
Braintree\Configuration::environment('sandbox');
Braintree\Configuration::merchantId('merchantId');
Braintree\Configuration::publicKey('publicKey');
Braintree\Configuration::privateKey('privateKey');

$result = Braintree\Transaction::sale(array(
    'amount' => '1000.00',
    'creditCard' => array(
        'number' => '5105105105105100',
        'expirationDate' => '05/12',
    ),
));

if ($result->success) {
    print_r('success!: '.$result->transaction->id);
} elseif ($result->transaction) {
    print_r('Error processing transaction:');
    print_r("\n  code: ".$result->transaction->processorResponseCode);
    print_r("\n  text: ".$result->transaction->processorResponseText);
} else {
    print_r("Validation errors: \n");
    print_r($result->errors->deepAll());
}
