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

```php
<?php

require_once 'PATH_TO_BRAINTREE/lib/Braintree.php';

Braintree_Configuration::environment('sandbox');
Braintree_Configuration::merchantId('your_merchant_id');
Braintree_Configuration::publicKey('your_public_key');
Braintree_Configuration::privateKey('your_private_key');

$result = Braintree_Transaction::sale(array(
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

## Looping Through Collections of Objects
Iterating through the results of a transaction or customer search will fetch the individual object details.

```php
<?php


$now = new Datetime();
$past = clone $now;
$past = $past->modify("-2 days");

//Lets get all the sales in the past 2 days.
$collection = Braintree_Transaction::search(array(
  Braintree_TransactionSearch::createdAt()->between($past, $now),
  Braintree_TransactionSearch::type()->is(Braintree_Transaction::SALE)
));

//loop throgh the collection to get access to individual transation objects.
foreach ($collection as $transaction) {
    print_r("transactionId ". $transaction->id . "\n");
    print_r("firstName: " . $transaction->customerDetails->firstName . "\n");
    print_r("amount: $" . $transaction->amount . "\n");
    print_r("paymentInstrument: " . $transaction->paymentInstrumentType . "\n ");
}

?>
```
## Finding Customer Payment Methods
If you offer multiple payment types. E.G. CreditCards, PayPal, and Apple Pay. You can use the paymentMethods() lookup to find all options easily. 

```php
<?php

//lookup a customer with multiple saved paymentMethods
$customer = Braintree_Customer::find('a_customer_id');

//loop throgh all of the payment methods.
foreach ($custtomer->paymentMethods() as $paymentMethod) {
    print_r($paymentMethod);
}

?>
```
## Documentation

 * [Official documentation](https://developers.braintreepayments.com/php/sdk/server/overview)

## Open Source Attribution

A list of open source projects that help power Braintree can be found [here](https://www.braintreepayments.com/developers/open-source).

## License

See the LICENSE file.
