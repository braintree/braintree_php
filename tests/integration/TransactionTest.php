<?php namespace Braintree\Tests\Integration;

use Braintree\Address;
use Braintree\ApplePayCard;
use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\Dispute;
use Braintree\Error\Codes;
use Braintree\Exception\Authorization;
use Braintree\Gateway;
use Braintree\PaymentInstrumentType;
use Braintree\PaymentMethod;
use Braintree\Subscription;
use Braintree\Test\CreditCardNumbers;
use Braintree\Test\Nonces;
use Braintree\Test\TransactionAmounts;
use Braintree\Test\VenmoSdk;
use Braintree\Transaction;
use Braintree\TransparentRedirect;
use CreditCardNumbers_CardTypeIndicators;
use OAuthTestHelper;
use TestHelper;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    function testCloneTransaction()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'orderId'    => '123',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/2011',
            ),
            'customer'   => array(
                'firstName' => 'Dan',
            ),
            'billing'    => array(
                'firstName' => 'Carl',
            ),
            'shipping'   => array(
                'firstName' => 'Andrew',
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        $cloneResult = Transaction::cloneTransaction(
            $transaction->id,
            array(
                'amount'  => '123.45',
                'channel' => 'MyShoppingCartProvider',
                'options' => array('submitForSettlement' => false)
            )
        );
        TestHelper::assertPrintable($cloneResult);
        $this->assertTrue($cloneResult->success);
        $cloneTransaction = $cloneResult->transaction;
        $this->assertEquals('Dan', $cloneTransaction->customerDetails->firstName);
        $this->assertEquals('Carl', $cloneTransaction->billingDetails->firstName);
        $this->assertEquals('Andrew', $cloneTransaction->shippingDetails->firstName);
        $this->assertEquals('510510******5100', $cloneTransaction->creditCardDetails->maskedNumber);
        $this->assertEquals('authorized', $cloneTransaction->status);
        $this->assertEquals('123.45', $cloneTransaction->amount);
        $this->assertEquals('MyShoppingCartProvider', $cloneTransaction->channel);
    }

    function testCreateTransactionUsingNonce()
    {
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "creditCard" => array(
                "number"          => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear"  => "2099"
            ),
            "share"      => true
        ));

        $result = Transaction::sale(array(
            'amount'             => '47.00',
            'paymentMethodNonce' => $nonce
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Transaction::SALE, $transaction->type);
        $this->assertEquals('47.00', $transaction->amount);
    }

    function testGatewayCreateTransactionUsingNonce()
    {
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "creditCard" => array(
                "number"          => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear"  => "2099"
            ),
            "share"      => true
        ));

        $gateway = new Gateway(array(
            'environment' => 'development',
            'merchantId'  => 'integration_merchant_id',
            'publicKey'   => 'integration_public_key',
            'privateKey'  => 'integration_private_key'
        ));
        $result = $gateway->transaction()->sale(array(
            'amount'             => '47.00',
            'paymentMethodNonce' => $nonce
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Transaction::SALE, $transaction->type);
        $this->assertEquals('47.00', $transaction->amount);
    }

    function testCreateTransactionUsingFakeApplePayNonce()
    {
        $result = Transaction::sale(array(
            'amount'             => '47.00',
            'paymentMethodNonce' => Nonces::$applePayAmEx
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('47.00', $transaction->amount);
        $applePayDetails = $transaction->applePayCardDetails;
        $this->assertSame(ApplePayCard::AMEX, $applePayDetails->cardType);
        $this->assertContains("AmEx ", $applePayDetails->paymentInstrumentName);
        $this->assertTrue(intval($applePayDetails->expirationMonth) > 0);
        $this->assertTrue(intval($applePayDetails->expirationYear) > 0);
        $this->assertNotNull($applePayDetails->cardholderName);
    }

    function testCreateTransactionUsingFakeAndroidPayNonce()
    {
        $result = Transaction::sale(array(
            'amount'             => '47.00',
            'paymentMethodNonce' => Nonces::$androidPay
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('47.00', $transaction->amount);
        $androidPayCardDetails = $transaction->androidPayCardDetails;
        $this->assertSame(CreditCard::DISCOVER, $androidPayCardDetails->cardType);
        $this->assertSame("1117", $androidPayCardDetails->last4);
        $this->assertNull($androidPayCardDetails->token);
        $this->assertSame(CreditCard::DISCOVER, $androidPayCardDetails->virtualCardType);
        $this->assertSame("1117", $androidPayCardDetails->virtualCardLast4);
        $this->assertSame(CreditCard::VISA, $androidPayCardDetails->sourceCardType);
        $this->assertSame("1111", $androidPayCardDetails->sourceCardLast4);
        $this->assertContains('android_pay', $androidPayCardDetails->imageUrl);
        $this->assertTrue(intval($androidPayCardDetails->expirationMonth) > 0);
        $this->assertTrue(intval($androidPayCardDetails->expirationYear) > 0);
    }

    function testCreateTransactionUsingFakeCoinbaseNonce()
    {
        $result = Transaction::sale(array(
            'amount'             => '17.00',
            'paymentMethodNonce' => Nonces::$coinbase
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertNotNull($transaction->coinbaseDetails);
        $this->assertNotNull($transaction->coinbaseDetails->userId);
        $this->assertNotNull($transaction->coinbaseDetails->userName);
        $this->assertNotNull($transaction->coinbaseDetails->userEmail);
    }

    function testCreateTransactionReturnsPaymentInstrumentType()
    {
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "creditCard" => array(
                "number"          => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear"  => "2099"
            ),
            "share"      => true
        ));

        $result = Transaction::sale(array(
            'amount'             => '47.00',
            'paymentMethodNonce' => $nonce
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(PaymentInstrumentType::CREDIT_CARD, $transaction->paymentInstrumentType);
    }

    function testCloneTransactionAndSubmitForSettlement()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/2011',
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        $cloneResult = Transaction::cloneTransaction($transaction->id,
            array('amount' => '123.45', 'options' => array('submitForSettlement' => true)));
        $cloneTransaction = $cloneResult->transaction;
        $this->assertEquals('submitted_for_settlement', $cloneTransaction->status);
    }

    function testCloneWithValidations()
    {
        $result = Transaction::credit(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/2011'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        $cloneResult = Transaction::cloneTransaction($transaction->id, array('amount' => '123.45'));
        $this->assertFalse($cloneResult->success);

        $baseErrors = $cloneResult->errors->forKey('transaction')->onAttribute('base');

        $this->assertEquals(Codes::TRANSACTION_CANNOT_CLONE_CREDIT, $baseErrors[0]->code);
    }

    function testSale()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertNotNull($transaction->processorAuthorizationCode);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
        $this->assertEquals('The Cardholder', $transaction->creditCardDetails->cardholderName);
    }

    function testSaleWithAccessToken()
    {
        $credentials = OAuthTestHelper::createCredentials(array(
            'clientId'     => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
            'merchantId'   => 'integration_merchant_id',
        ));

        $gateway = new Gateway(array(
            'accessToken' => $credentials->accessToken,
        ));

        $result = $gateway->transaction()->sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertNotNull($transaction->processorAuthorizationCode);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
        $this->assertEquals('The Cardholder', $transaction->creditCardDetails->cardholderName);
    }

    function testSaleWithRiskData()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertNotNull($transaction->riskData);
        $this->assertNotNull($transaction->riskData->decision);
    }

    function testRecurring()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'recurring'  => true,
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(true, $transaction->recurring);
    }

    function testSale_withServiceFee()
    {
        $result = Transaction::sale(array(
            'amount'            => '10.00',
            'merchantAccountId' => TestHelper::nonDefaultSubMerchantAccountId(),
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'serviceFeeAmount'  => '1.00'
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('1.00', $transaction->serviceFeeAmount);
    }

    function testSale_isInvalidIfTransactionMerchantAccountIsNotSub()
    {
        $result = Transaction::sale(array(
            'amount'            => '10.00',
            'merchantAccountId' => TestHelper::nonDefaultMerchantAccountId(),
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'serviceFeeAmount'  => '1.00'
        ));
        $this->assertFalse($result->success);
        $transaction = $result->transaction;
        $serviceFeeErrors = $result->errors->forKey('transaction')->onAttribute('serviceFeeAmount');
        $this->assertEquals(Codes::TRANSACTION_SERVICE_FEE_AMOUNT_NOT_ALLOWED_ON_MASTER_MERCHANT_ACCOUNT,
            $serviceFeeErrors[0]->code);
    }

    function testSale_isInvalidIfSubMerchantAccountHasNoServiceFee()
    {
        $result = Transaction::sale(array(
            'amount'            => '10.00',
            'merchantAccountId' => TestHelper::nonDefaultSubMerchantAccountId(),
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertFalse($result->success);
        $transaction = $result->transaction;
        $serviceFeeErrors = $result->errors->forKey('transaction')->onAttribute('merchantAccountId');
        $this->assertEquals(Codes::TRANSACTION_SUB_MERCHANT_ACCOUNT_REQUIRES_SERVICE_FEE_AMOUNT,
            $serviceFeeErrors[0]->code);
    }

    function testSale_withVenmoSdkSession()
    {
        $result = Transaction::sale(array(
            'amount'     => '10.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'options'    => array(
                'venmoSdkSession' => VenmoSdk::getTestSession()
            )
        ));
        $this->assertEquals(true, $result->success);
        $transaction = $result->transaction;
        $this->assertEquals(true, $transaction->creditCardDetails->venmoSdk);
    }

    function testSale_withVenmoSdkPaymentMethodCode()
    {
        $result = Transaction::sale(array(
            'amount'                    => '10.00',
            'venmoSdkPaymentMethodCode' => VenmoSdk::$visaPaymentMethodCode
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals("411111", $transaction->creditCardDetails->bin);
    }

    function testSale_withLevel2Attributes()
    {
        $result = Transaction::sale(array(
            'amount'              => '100.00',
            'creditCard'          => array(
                'cardholderName' => 'The Cardholder',
                'expirationDate' => '05/2011',
                'number'         => '5105105105105100'
            ),
            'taxExempt'           => true,
            'taxAmount'           => '10.00',
            'purchaseOrderNumber' => '12345'
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        $this->assertTrue($transaction->taxExempt);
        $this->assertEquals('10.00', $transaction->taxAmount);
        $this->assertEquals('12345', $transaction->purchaseOrderNumber);
    }

    function testSale_withInvalidTaxAmountAttribute()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'expirationDate' => '05/2011',
                'number'         => '5105105105105100'
            ),
            'taxAmount'  => 'abc'
        ));

        $this->assertFalse($result->success);

        $taxAmountErrors = $result->errors->forKey('transaction')->onAttribute('taxAmount');
        $this->assertEquals(Codes::TRANSACTION_TAX_AMOUNT_FORMAT_IS_INVALID, $taxAmountErrors[0]->code);
    }

    function testSale_withServiceFeeTooLarge()
    {
        $result = Transaction::sale(array(
            'amount'            => '10.00',
            'merchantAccountId' => TestHelper::nonDefaultSubMerchantAccountId(),
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'serviceFeeAmount'  => '20.00'
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('serviceFeeAmount');
        $this->assertEquals(Codes::TRANSACTION_SERVICE_FEE_AMOUNT_IS_TOO_LARGE, $errors[0]->code);
    }

    function testSale_withTooLongPurchaseOrderAttribute()
    {
        $result = Transaction::sale(array(
            'amount'              => '100.00',
            'creditCard'          => array(
                'cardholderName' => 'The Cardholder',
                'expirationDate' => '05/2011',
                'number'         => '5105105105105100'
            ),
            'purchaseOrderNumber' => 'aaaaaaaaaaaaaaaaaa'
        ));

        $this->assertFalse($result->success);

        $purchaseOrderNumberErrors = $result->errors->forKey('transaction')->onAttribute('purchaseOrderNumber');
        $this->assertEquals(Codes::TRANSACTION_PURCHASE_ORDER_NUMBER_IS_TOO_LONG,
            $purchaseOrderNumberErrors[0]->code);
    }

    function testSale_withInvalidPurchaseOrderNumber()
    {
        $result = Transaction::sale(array(
            'amount'              => '100.00',
            'creditCard'          => array(
                'cardholderName' => 'The Cardholder',
                'expirationDate' => '05/2011',
                'number'         => '5105105105105100'
            ),
            'purchaseOrderNumber' => "\x80\x90\xA0"
        ));

        $this->assertFalse($result->success);

        $purchaseOrderNumberErrors = $result->errors->forKey('transaction')->onAttribute('purchaseOrderNumber');
        $this->assertEquals(Codes::TRANSACTION_PURCHASE_ORDER_NUMBER_IS_INVALID,
            $purchaseOrderNumberErrors[0]->code);
    }

    function testSale_withAllAttributes()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'orderId'    => '123',
            'channel'    => 'MyShoppingCardProvider',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number'         => '5105105105105100',
                'expirationDate' => '05/2011',
                'cvv'            => '123'
            ),
            'customer'   => array(
                'firstName' => 'Dan',
                'lastName'  => 'Smith',
                'company'   => 'Braintree',
                'email'     => 'dan@example.com',
                'phone'     => '419-555-1234',
                'fax'       => '419-555-1235',
                'website'   => 'http://braintreepayments.com'
            ),
            'billing'    => array(
                'firstName'          => 'Carl',
                'lastName'           => 'Jones',
                'company'            => 'Braintree',
                'streetAddress'      => '123 E Main St',
                'extendedAddress'    => 'Suite 403',
                'locality'           => 'Chicago',
                'region'             => 'IL',
                'postalCode'         => '60622',
                'countryName'        => 'United States of America',
                'countryCodeAlpha2'  => 'US',
                'countryCodeAlpha3'  => 'USA',
                'countryCodeNumeric' => '840'
            ),
            'shipping'   => array(
                'firstName'          => 'Andrew',
                'lastName'           => 'Mason',
                'company'            => 'Braintree',
                'streetAddress'      => '456 W Main St',
                'extendedAddress'    => 'Apt 2F',
                'locality'           => 'Bartlett',
                'region'             => 'IL',
                'postalCode'         => '60103',
                'countryName'        => 'United States of America',
                'countryCodeAlpha2'  => 'US',
                'countryCodeAlpha3'  => 'USA',
                'countryCodeNumeric' => '840'
            )
        ));
        TestHelper::assertPrintable($result);
        $this->assertTrue($result->success);
        $transaction = $result->transaction;

        $this->assertNotNull($transaction->id);
        $this->assertNotNull($transaction->createdAt);
        $this->assertNotNull($transaction->updatedAt);
        $this->assertNull($transaction->refundId);

        $this->assertEquals(TestHelper::defaultMerchantAccountId(), $transaction->merchantAccountId);
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('USD', $transaction->currencyIsoCode);
        $this->assertEquals('123', $transaction->orderId);
        $this->assertEquals('MyShoppingCardProvider', $transaction->channel);
        $this->assertEquals('MasterCard', $transaction->creditCardDetails->cardType);
        $this->assertEquals('1000', $transaction->processorResponseCode);
        $this->assertEquals('Approved', $transaction->processorResponseText);
        $this->assertNull($transaction->voiceReferralNumber);
        $this->assertFalse($transaction->taxExempt);

        $this->assertEquals('M', $transaction->avsPostalCodeResponseCode);
        $this->assertEquals('M', $transaction->avsStreetAddressResponseCode);
        $this->assertEquals('M', $transaction->cvvResponseCode);

        $this->assertEquals('Dan', $transaction->customerDetails->firstName);
        $this->assertEquals('Smith', $transaction->customerDetails->lastName);
        $this->assertEquals('Braintree', $transaction->customerDetails->company);
        $this->assertEquals('dan@example.com', $transaction->customerDetails->email);
        $this->assertEquals('419-555-1234', $transaction->customerDetails->phone);
        $this->assertEquals('419-555-1235', $transaction->customerDetails->fax);
        $this->assertEquals('http://braintreepayments.com', $transaction->customerDetails->website);

        $this->assertEquals('Carl', $transaction->billingDetails->firstName);
        $this->assertEquals('Jones', $transaction->billingDetails->lastName);
        $this->assertEquals('Braintree', $transaction->billingDetails->company);
        $this->assertEquals('123 E Main St', $transaction->billingDetails->streetAddress);
        $this->assertEquals('Suite 403', $transaction->billingDetails->extendedAddress);
        $this->assertEquals('Chicago', $transaction->billingDetails->locality);
        $this->assertEquals('IL', $transaction->billingDetails->region);
        $this->assertEquals('60622', $transaction->billingDetails->postalCode);
        $this->assertEquals('United States of America', $transaction->billingDetails->countryName);
        $this->assertEquals('US', $transaction->billingDetails->countryCodeAlpha2);
        $this->assertEquals('USA', $transaction->billingDetails->countryCodeAlpha3);
        $this->assertEquals('840', $transaction->billingDetails->countryCodeNumeric);

        $this->assertEquals('Andrew', $transaction->shippingDetails->firstName);
        $this->assertEquals('Mason', $transaction->shippingDetails->lastName);
        $this->assertEquals('Braintree', $transaction->shippingDetails->company);
        $this->assertEquals('456 W Main St', $transaction->shippingDetails->streetAddress);
        $this->assertEquals('Apt 2F', $transaction->shippingDetails->extendedAddress);
        $this->assertEquals('Bartlett', $transaction->shippingDetails->locality);
        $this->assertEquals('IL', $transaction->shippingDetails->region);
        $this->assertEquals('60103', $transaction->shippingDetails->postalCode);
        $this->assertEquals('United States of America', $transaction->shippingDetails->countryName);
        $this->assertEquals('US', $transaction->shippingDetails->countryCodeAlpha2);
        $this->assertEquals('USA', $transaction->shippingDetails->countryCodeAlpha3);
        $this->assertEquals('840', $transaction->shippingDetails->countryCodeNumeric);

        $this->assertNotNull($transaction->processorAuthorizationCode);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
        $this->assertEquals('510510******5100', $transaction->creditCardDetails->maskedNumber);
        $this->assertEquals('The Cardholder', $transaction->creditCardDetails->cardholderName);
        $this->assertEquals('05', $transaction->creditCardDetails->expirationMonth);
        $this->assertEquals('2011', $transaction->creditCardDetails->expirationYear);
        $this->assertNotNull($transaction->creditCardDetails->imageUrl);
    }

    function testSale_withCustomFields()
    {
        $result = Transaction::sale(array(
            'amount'       => '100.00',
            'creditCard'   => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'customFields' => array(
                'store_me' => 'custom value'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $customFields = $transaction->customFields;
        $this->assertEquals('custom value', $customFields['store_me']);
    }

    function testSale_withExpirationMonthAndYear()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'          => '5105105105105100',
                'expirationMonth' => '5',
                'expirationYear'  => '2012'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('05', $transaction->creditCardDetails->expirationMonth);
        $this->assertEquals('2012', $transaction->creditCardDetails->expirationYear);
    }

    function testSale_underscoresAllCustomFields()
    {
        $result = Transaction::sale(array(
            'amount'       => '100.00',
            'creditCard'   => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'customFields' => array(
                'storeMe' => 'custom value'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $customFields = $transaction->customFields;
        $this->assertEquals('custom value', $customFields['store_me']);
    }

    function testSale_withInvalidCustomField()
    {
        $result = Transaction::sale(array(
            'amount'       => '100.00',
            'creditCard'   => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'customFields' => array(
                'invalidKey' => 'custom value'
            )
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('customFields');
        $this->assertEquals(Codes::TRANSACTION_CUSTOM_FIELD_IS_INVALID, $errors[0]->code);
        $this->assertEquals('Custom field is invalid: invalidKey.', $errors[0]->message);
    }

    function testSale_withMerchantAccountId()
    {
        $result = Transaction::sale(array(
            'amount'            => '100.00',
            'merchantAccountId' => TestHelper::nonDefaultMerchantAccountId(),
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(TestHelper::nonDefaultMerchantAccountId(), $transaction->merchantAccountId);
    }

    function testSale_withoutMerchantAccountIdFallsBackToDefault()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(TestHelper::defaultMerchantAccountId(), $transaction->merchantAccountId);
    }

    function testSale_withShippingAddressId()
    {
        $customer = Customer::create(array(
            'firstName'  => 'Mike',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/12'
            )
        ))->customer;

        $address = Address::create(array(
            'customerId'    => $customer->id,
            'streetAddress' => '123 Fake St.'
        ))->address;

        $result = Transaction::sale(array(
            'amount'            => '100.00',
            'customerId'        => $customer->id,
            'shippingAddressId' => $address->id
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('123 Fake St.', $transaction->shippingDetails->streetAddress);
        $this->assertEquals($address->id, $transaction->shippingDetails->id);
    }

    function testSale_withBillingAddressId()
    {
        $customer = Customer::create(array(
            'firstName'  => 'Mike',
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/12'
            )
        ))->customer;

        $address = Address::create(array(
            'customerId'    => $customer->id,
            'streetAddress' => '123 Fake St.'
        ))->address;

        $result = Transaction::sale(array(
            'amount'           => '100.00',
            'customerId'       => $customer->id,
            'billingAddressId' => $address->id
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('123 Fake St.', $transaction->billingDetails->streetAddress);
        $this->assertEquals($address->id, $transaction->billingDetails->id);
    }

    function testSaleNoValidate()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
    }

    function testSale_withProcessorDecline()
    {
        $result = Transaction::sale(array(
            'amount'     => TransactionAmounts::$decline,
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(Transaction::PROCESSOR_DECLINED, $result->transaction->status);
        $this->assertEquals(2000, $result->transaction->processorResponseCode);
        $this->assertEquals("Do Not Honor", $result->transaction->processorResponseText);
        $this->assertEquals("2000 : Do Not Honor", $result->transaction->additionalProcessorResponse);
    }

    function testSale_withExistingCustomer()
    {
        $customer = Customer::create(array(
            'firstName' => 'Mike',
            'lastName'  => 'Jones',
            'company'   => 'Jones Co.',
            'email'     => 'mike.jones@example.com',
            'phone'     => '419.555.1234',
            'fax'       => '419.555.1235',
            'website'   => 'http://example.com'
        ))->customer;

        $transaction = Transaction::sale(array(
            'amount'     => '100.00',
            'customerId' => $customer->id,
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/12'
            )
        ))->transaction;
        $this->assertEquals($transaction->creditCardDetails->maskedNumber, '401288******1881');
        $this->assertNull($transaction->vaultCreditCard());
    }

    function testSale_andStoreShippingAddressInVault()
    {
        $customer = Customer::create(array(
            'firstName' => 'Mike',
            'lastName'  => 'Jones',
            'company'   => 'Jones Co.',
            'email'     => 'mike.jones@example.com',
            'phone'     => '419.555.1234',
            'fax'       => '419.555.1235',
            'website'   => 'http://example.com'
        ))->customer;

        $transaction = Transaction::sale(array(
            'amount'     => '100.00',
            'customerId' => $customer->id,
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/12'
            ),
            'shipping'   => array(
                'firstName' => 'Darren',
                'lastName'  => 'Stevens'
            ),
            'options'    => array(
                'storeInVault'                => true,
                'storeShippingAddressInVault' => true
            )
        ))->transaction;

        $customer = Customer::find($customer->id);
        $this->assertEquals('Darren', $customer->addresses[0]->firstName);
        $this->assertEquals('Stevens', $customer->addresses[0]->lastName);
    }

    function testSale_withExistingCustomer_storeInVault()
    {
        $customer = Customer::create(array(
            'firstName' => 'Mike',
            'lastName'  => 'Jones',
            'company'   => 'Jones Co.',
            'email'     => 'mike.jones@example.com',
            'phone'     => '419.555.1234',
            'fax'       => '419.555.1235',
            'website'   => 'http://example.com'
        ))->customer;

        $transaction = Transaction::sale(array(
            'amount'     => '100.00',
            'customerId' => $customer->id,
            'creditCard' => array(
                'cardholderName' => 'The Cardholder',
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/12'
            ),
            'options'    => array(
                'storeInVault' => true
            )
        ))->transaction;
        $this->assertEquals($transaction->creditCardDetails->maskedNumber, '401288******1881');
        $this->assertEquals($transaction->vaultCreditCard()->maskedNumber, '401288******1881');
    }

    function testCredit()
    {
        $result = Transaction::credit(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Transaction::SUBMITTED_FOR_SETTLEMENT, $transaction->status);
        $this->assertEquals(Transaction::CREDIT, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
    }

    function testCreditNoValidate()
    {
        $transaction = Transaction::creditNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Transaction::CREDIT, $transaction->type);
        $this->assertEquals(Transaction::SUBMITTED_FOR_SETTLEMENT, $transaction->status);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
    }

    function testCredit_withMerchantAccountId()
    {
        $result = Transaction::credit(array(
            'amount'            => '100.00',
            'merchantAccountId' => TestHelper::nonDefaultMerchantAccountId(),
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(TestHelper::nonDefaultMerchantAccountId(), $transaction->merchantAccountId);
    }

    function testCredit_withoutMerchantAccountIdFallsBackToDefault()
    {
        $result = Transaction::credit(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(TestHelper::defaultMerchantAccountId(), $transaction->merchantAccountId);
    }

    function testCredit_withServiceFeeNotAllowed()
    {
        $result = Transaction::credit(array(
            'amount'           => '100.00',
            'creditCard'       => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'serviceFeeAmount' => '12.75'
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(Codes::TRANSACTION_SERVICE_FEE_IS_NOT_ALLOWED_ON_CREDITS, $errors[0]->code);
    }

    function testSubmitForSettlement_nullAmount()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $submitResult = Transaction::submitForSettlement($transaction->id);
        $this->assertEquals(true, $submitResult->success);
        $this->assertEquals(Transaction::SUBMITTED_FOR_SETTLEMENT, $submitResult->transaction->status);
        $this->assertEquals('100.00', $submitResult->transaction->amount);
    }

    function testSubmitForSettlement_amountLessThanServiceFee()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'            => '10.00',
            'merchantAccountId' => TestHelper::nonDefaultSubMerchantAccountId(),
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'serviceFeeAmount'  => '5.00'
        ));
        $submitResult = Transaction::submitForSettlement($transaction->id, '1.00');
        $errors = $submitResult->errors->forKey('transaction')->onAttribute('amount');
        $this->assertEquals(Codes::TRANSACTION_SETTLEMENT_AMOUNT_IS_LESS_THAN_SERVICE_FEE_AMOUNT,
            $errors[0]->code);
    }

    function testSubmitForSettlement_withAmount()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $submitResult = Transaction::submitForSettlement($transaction->id, '50.00');
        $this->assertEquals(true, $submitResult->success);
        $this->assertEquals(Transaction::SUBMITTED_FOR_SETTLEMENT, $submitResult->transaction->status);
        $this->assertEquals('50.00', $submitResult->transaction->amount);
    }

    function testSubmitForSettlementNoValidate_whenValidWithoutAmount()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $submittedTransaction = Transaction::submitForSettlementNoValidate($transaction->id);
        $this->assertEquals(Transaction::SUBMITTED_FOR_SETTLEMENT, $submittedTransaction->status);
        $this->assertEquals('100.00', $submittedTransaction->amount);
    }

    function testSubmitForSettlementNoValidate_whenValidWithAmount()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $submittedTransaction = Transaction::submitForSettlementNoValidate($transaction->id, '99.00');
        $this->assertEquals(Transaction::SUBMITTED_FOR_SETTLEMENT, $submittedTransaction->status);
        $this->assertEquals('99.00', $submittedTransaction->amount);
    }

    function testSubmitForSettlementNoValidate_whenInvalid()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $this->setExpectedException('Exception_ValidationsFailed');
        $submittedTransaction = Transaction::submitForSettlementNoValidate($transaction->id, '101.00');
    }

    function testVoid()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $voidResult = Transaction::void($transaction->id);
        $this->assertEquals(true, $voidResult->success);
        $this->assertEquals(Transaction::VOIDED, $voidResult->transaction->status);
    }

    function test_countryValidationinconsistency()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'billing'    => array(
                'countryCodeAlpha2' => 'AS',
                'countryCodeAlpha3' => 'USA'
            )
        ));
        $this->assertFalse($result->success);

        $errors = $result->errors->forKey('transaction')->forKey('billing')->onAttribute('base');
        $this->assertEquals(Codes::ADDRESS_INCONSISTENT_COUNTRY, $errors[0]->code);
    }

    function test_countryValidationincorrectAlpha2()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'billing'    => array(
                'countryCodeAlpha2' => 'ZZ'
            )
        ));
        $this->assertFalse($result->success);

        $errors = $result->errors->forKey('transaction')->forKey('billing')->onAttribute('countryCodeAlpha2');
        $this->assertEquals(Codes::ADDRESS_COUNTRY_CODE_ALPHA2_IS_NOT_ACCEPTED, $errors[0]->code);
    }

    function test_countryValidationincorrectAlpha3()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'billing'    => array(
                'countryCodeAlpha3' => 'ZZZ'
            )
        ));
        $this->assertFalse($result->success);

        $errors = $result->errors->forKey('transaction')->forKey('billing')->onAttribute('countryCodeAlpha3');
        $this->assertEquals(Codes::ADDRESS_COUNTRY_CODE_ALPHA3_IS_NOT_ACCEPTED, $errors[0]->code);
    }

    function test_countryValidationincorrectNumericCode()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'billing'    => array(
                'countryCodeNumeric' => '000'
            )
        ));
        $this->assertFalse($result->success);

        $errors = $result->errors->forKey('transaction')->forKey('billing')->onAttribute('countryCodeNumeric');
        $this->assertEquals(Codes::ADDRESS_COUNTRY_CODE_NUMERIC_IS_NOT_ACCEPTED, $errors[0]->code);
    }

    function testVoid_withValidationError()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $voided = Transaction::voidNoValidate($transaction->id);
        $this->assertEquals(Transaction::VOIDED, $voided->status);
        $result = Transaction::void($transaction->id);
        $this->assertEquals(false, $result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(Codes::TRANSACTION_CANNOT_BE_VOIDED, $errors[0]->code);
    }

    function testVoidNoValidate()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $voided = Transaction::voidNoValidate($transaction->id);
        $this->assertEquals(Transaction::VOIDED, $voided->status);
    }

    function testVoidNoValidate_throwsIfNotInvalid()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $voided = Transaction::voidNoValidate($transaction->id);
        $this->assertEquals(Transaction::VOIDED, $voided->status);
        $this->setExpectedException('Exception_ValidationsFailed');
        $voided = Transaction::voidNoValidate($transaction->id);
    }

    function testFind()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $transaction = Transaction::find($result->transaction->id);
        $this->assertEquals(Transaction::AUTHORIZED, $transaction->status);
        $this->assertEquals(Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertEquals('510510', $transaction->creditCardDetails->bin);
        $this->assertEquals('5100', $transaction->creditCardDetails->last4);
    }

    function testFindExposesDisbursementDetails()
    {
        $transaction = Transaction::find("deposittransaction");

        $this->assertEquals(true, $transaction->isDisbursed());

        $disbursementDetails = $transaction->disbursementDetails;
        $this->assertEquals('100.00', $disbursementDetails->settlementAmount);
        $this->assertEquals('USD', $disbursementDetails->settlementCurrencyIsoCode);
        $this->assertEquals('1', $disbursementDetails->settlementCurrencyExchangeRate);
        $this->assertEquals(false, $disbursementDetails->fundsHeld);
        $this->assertEquals(true, $disbursementDetails->success);
        $this->assertEquals(new \DateTime('2013-04-10'), $disbursementDetails->disbursementDate);
    }

    function testFindExposesDisputes()
    {
        $transaction = Transaction::find("disputedtransaction");

        $dispute = $transaction->disputes[0];
        $this->assertEquals('250.00', $dispute->amount);
        $this->assertEquals('USD', $dispute->currencyIsoCode);
        $this->assertEquals(Dispute::FRAUD, $dispute->reason);
        $this->assertEquals(Dispute::WON, $dispute->status);
        $this->assertEquals(new \DateTime('2014-03-01'), $dispute->receivedDate);
        $this->assertEquals(new \DateTime('2014-03-21'), $dispute->replyByDate);
        $this->assertEquals("disputedtransaction", $dispute->transactionDetails->id);
        $this->assertEquals("1000.00", $dispute->transactionDetails->amount);
    }

    function testFindExposesThreeDSecureInfo()
    {
        $transaction = Transaction::find("threedsecuredtransaction");

        $info = $transaction->threeDSecureInfo;
        $this->assertEquals("Y", $info->enrolled);
        $this->assertEquals("authenticate_successful", $info->status);
        $this->assertTrue($info->liabilityShifted);
        $this->assertTrue($info->liabilityShiftPossible);
    }

    function testFindExposesNullThreeDSecureInfo()
    {
        $transaction = Transaction::find("settledtransaction");

        $this->assertNull($transaction->threeDSecureInfo);
    }

    function testFindExposesRetrievals()
    {
        $transaction = Transaction::find("retrievaltransaction");

        $dispute = $transaction->disputes[0];
        $this->assertEquals('1000.00', $dispute->amount);
        $this->assertEquals('USD', $dispute->currencyIsoCode);
        $this->assertEquals(Dispute::RETRIEVAL, $dispute->reason);
        $this->assertEquals(Dispute::OPEN, $dispute->status);
        $this->assertEquals("retrievaltransaction", $dispute->transactionDetails->id);
        $this->assertEquals("1000.00", $dispute->transactionDetails->amount);
    }

    function testFindExposesPayPalDetails()
    {
        $transaction = Transaction::find("settledtransaction");
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->assertNotNull($transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->authorizationId);
        $this->assertNotNull($transaction->paypalDetails->payerId);
        $this->assertNotNull($transaction->paypalDetails->payerFirstName);
        $this->assertNotNull($transaction->paypalDetails->payerLastName);
        $this->assertNotNull($transaction->paypalDetails->sellerProtectionStatus);
        $this->assertNotNull($transaction->paypalDetails->captureId);
        $this->assertNotNull($transaction->paypalDetails->refundId);
    }

    function testSale_storeInVault()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'cardholderName' => 'Card Holder',
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'customer'   => array(
                'firstName' => 'Dan',
                'lastName'  => 'Smith',
                'company'   => 'Braintree',
                'email'     => 'dan@example.com',
                'phone'     => '419-555-1234',
                'fax'       => '419-555-1235',
                'website'   => 'http://getbraintree.com'
            ),
            'options'    => array(
                'storeInVault' => true
            )
        ));
        $this->assertNotNull($transaction->creditCardDetails->token);
        $creditCard = $transaction->vaultCreditCard();
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('Card Holder', $creditCard->cardholderName);
        $customer = $transaction->vaultCustomer();
        $this->assertEquals('Dan', $customer->firstName);
        $this->assertEquals('Smith', $customer->lastName);
        $this->assertEquals('Braintree', $customer->company);
        $this->assertEquals('dan@example.com', $customer->email);
        $this->assertEquals('419-555-1234', $customer->phone);
        $this->assertEquals('419-555-1235', $customer->fax);
        $this->assertEquals('http://getbraintree.com', $customer->website);
    }

    function testSale_storeInVaultOnSuccessWithSuccessfulTransaction()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'cardholderName' => 'Card Holder',
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'customer'   => array(
                'firstName' => 'Dan',
                'lastName'  => 'Smith',
                'company'   => 'Braintree',
                'email'     => 'dan@example.com',
                'phone'     => '419-555-1234',
                'fax'       => '419-555-1235',
                'website'   => 'http://getbraintree.com'
            ),
            'options'    => array(
                'storeInVaultOnSuccess' => true
            )
        ));
        $this->assertNotNull($transaction->creditCardDetails->token);
        $creditCard = $transaction->vaultCreditCard();
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('Card Holder', $creditCard->cardholderName);
        $customer = $transaction->vaultCustomer();
        $this->assertEquals('Dan', $customer->firstName);
        $this->assertEquals('Smith', $customer->lastName);
        $this->assertEquals('Braintree', $customer->company);
        $this->assertEquals('dan@example.com', $customer->email);
        $this->assertEquals('419-555-1234', $customer->phone);
        $this->assertEquals('419-555-1235', $customer->fax);
        $this->assertEquals('http://getbraintree.com', $customer->website);
    }

    function testSale_storeInVaultOnSuccessWithFailedTransaction()
    {
        $result = Transaction::sale(array(
            'amount'     => TransactionAmounts::$decline,
            'creditCard' => array(
                'cardholderName' => 'Card Holder',
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'customer'   => array(
                'firstName' => 'Dan',
                'lastName'  => 'Smith',
                'company'   => 'Braintree',
                'email'     => 'dan@example.com',
                'phone'     => '419-555-1234',
                'fax'       => '419-555-1235',
                'website'   => 'http://getbraintree.com'
            ),
            'options'    => array(
                'storeInVaultOnSuccess' => true
            )
        ));
        $transaction = $result->transaction;
        $this->assertNull($transaction->creditCardDetails->token);
        $this->assertNull($transaction->vaultCreditCard());
        $this->assertNull($transaction->customerDetails->id);
        $this->assertNull($transaction->vaultCustomer());
    }

    function testSale_withFraudParams()
    {
        $result = Transaction::sale(array(
            'deviceSessionId' => '123abc',
            'fraudMerchantId' => '456',
            'amount'          => '100.00',
            'creditCard'      => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
            )
        ));

        $this->assertTrue($result->success);
    }

    function testSale_withDescriptor()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'descriptor' => array(
                'name'  => '123*123456789012345678',
                'phone' => '3334445555',
                'url'   => 'ebay.com'
            )
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('123*123456789012345678', $transaction->descriptor->name);
        $this->assertEquals('3334445555', $transaction->descriptor->phone);
        $this->assertEquals('ebay.com', $transaction->descriptor->url);
    }

    function testSale_withDescriptorValidation()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'descriptor' => array(
                'name'  => 'badcompanyname12*badproduct12',
                'phone' => '%bad4445555',
                'url'   => '12345678901234'
            )
        ));
        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $errors = $result->errors->forKey('transaction')->forKey('descriptor')->onAttribute('name');
        $this->assertEquals(Codes::DESCRIPTOR_NAME_FORMAT_IS_INVALID, $errors[0]->code);

        $errors = $result->errors->forKey('transaction')->forKey('descriptor')->onAttribute('phone');
        $this->assertEquals(Codes::DESCRIPTOR_PHONE_FORMAT_IS_INVALID, $errors[0]->code);

        $errors = $result->errors->forKey('transaction')->forKey('descriptor')->onAttribute('url');
        $this->assertEquals(Codes::DESCRIPTOR_URL_FORMAT_IS_INVALID, $errors[0]->code);
    }

    function testSale_withHoldInEscrow()
    {
        $result = Transaction::sale(array(
            'merchantAccountId' => TestHelper::nonDefaultSubMerchantAccountId(),
            'amount'            => '100.00',
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'options'           => array(
                'holdInEscrow' => true
            ),
            'serviceFeeAmount'  => '1.00'
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Transaction::ESCROW_HOLD_PENDING, $transaction->escrowStatus);
    }

    function testSale_withHoldInEscrowFailsForMasterMerchantAccount()
    {
        $result = Transaction::sale(array(
            'merchantAccountId' => TestHelper::nonDefaultMerchantAccountId(),
            'amount'            => '100.00',
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'options'           => array(
                'holdInEscrow' => true
            )
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(
            Codes::TRANSACTION_CANNOT_HOLD_IN_ESCROW,
            $errors[0]->code
        );
    }

    function testSale_withThreeDSecureOptionRequired()
    {
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonce_for_new_card(array(
            "creditCard" => array(
                "number"          => "4111111111111111",
                "expirationMonth" => "11",
                "expirationYear"  => "2099"
            )
        ));

        $result = Transaction::sale(array(
            'merchantAccountId' => TestHelper::threeDSecureMerchantAccountId(),
            'amount'            => '100.00',
            'creditCard'        => array(
                'number'         => '4111111111111111',
                'expirationDate' => '05/09'
            ),
            'options'           => array(
                'three_d_secure' => array(
                    'required' => true
                )
            )
        ));
        $this->assertFalse($result->success);
        $this->assertEquals(Transaction::THREE_D_SECURE, $result->transaction->gatewayRejectionReason);
    }

    function testSale_withThreeDSecureToken()
    {
        $threeDSecureToken = TestHelper::create3DSVerification(
            TestHelper::threeDSecureMerchantAccountId(),
            array(
                'number'          => '4111111111111111',
                'expirationMonth' => '05',
                'expirationYear'  => '2009'
            )
        );
        $result = Transaction::sale(array(
            'merchantAccountId' => TestHelper::threeDSecureMerchantAccountId(),
            'amount'            => '100.00',
            'creditCard'        => array(
                'number'         => '4111111111111111',
                'expirationDate' => '05/09'
            ),
            'threeDSecureToken' => $threeDSecureToken
        ));
        $this->assertTrue($result->success);
    }

    function testSale_returnsErrorIfThreeDSecureToken()
    {
        $result = Transaction::sale(array(
            'merchantAccountId' => TestHelper::threeDSecureMerchantAccountId(),
            'amount'            => '100.00',
            'creditCard'        => array(
                'number'         => '4111111111111111',
                'expirationDate' => '05/09'
            ),
            'threeDSecureToken' => null
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('threeDSecureToken');
        $this->assertEquals(
            Codes::TRANSACTION_THREE_D_SECURE_TOKEN_IS_INVALID,
            $errors[0]->code
        );
    }

    function testSale_returnsErrorIf3dsLookupDataDoesNotMatchTransactionData()
    {
        $threeDSecureToken = TestHelper::create3DSVerification(
            TestHelper::threeDSecureMerchantAccountId(),
            array(
                'number'          => '4111111111111111',
                'expirationMonth' => '05',
                'expirationYear'  => '2009'
            )
        );

        $result = Transaction::sale(array(
            'merchantAccountId' => TestHelper::threeDSecureMerchantAccountId(),
            'amount'            => '100.00',
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/09'
            ),
            'threeDSecureToken' => $threeDSecureToken
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('threeDSecureToken');
        $this->assertEquals(
            Codes::TRANSACTION_THREE_D_SECURE_TRANSACTION_DATA_DOESNT_MATCH_VERIFY,
            $errors[0]->code
        );
    }

    function testHoldInEscrow_afterSale()
    {
        $result = Transaction::sale(array(
            'merchantAccountId' => TestHelper::nonDefaultSubMerchantAccountId(),
            'amount'            => '100.00',
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'serviceFeeAmount'  => '1.00'
        ));
        $result = Transaction::holdInEscrow($result->transaction->id);
        $this->assertTrue($result->success);
        $this->assertEquals(Transaction::ESCROW_HOLD_PENDING, $result->transaction->escrowStatus);
    }

    function testHoldInEscrow_afterSaleFailsWithMasterMerchantAccount()
    {
        $result = Transaction::sale(array(
            'merchantAccountId' => TestHelper::nonDefaultMerchantAccountId(),
            'amount'            => '100.00',
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $result = Transaction::holdInEscrow($result->transaction->id);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(
            Codes::TRANSACTION_CANNOT_HOLD_IN_ESCROW,
            $errors[0]->code
        );
    }

    function testSubmitForRelease_FromEscrow()
    {
        $transaction = $this->createEscrowedTransaction();
        $result = Transaction::releaseFromEscrow($transaction->id);
        $this->assertTrue($result->success);
        $this->assertEquals(Transaction::ESCROW_RELEASE_PENDING, $result->transaction->escrowStatus);
    }

    function testSubmitForRelease_fromEscrowFailsForTransactionsNotHeldInEscrow()
    {
        $result = Transaction::sale(array(
            'merchantAccountId' => TestHelper::nonDefaultMerchantAccountId(),
            'amount'            => '100.00',
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $result = Transaction::releaseFromEscrow($result->transaction->id);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(
            Codes::TRANSACTION_CANNOT_RELEASE_FROM_ESCROW,
            $errors[0]->code
        );
    }

    function testCancelRelease_fromEscrow()
    {
        $transaction = $this->createEscrowedTransaction();
        $result = Transaction::releaseFromEscrow($transaction->id);
        $result = Transaction::cancelRelease($transaction->id);
        $this->assertTrue($result->success);
        $this->assertEquals(
            Transaction::ESCROW_HELD,
            $result->transaction->escrowStatus
        );
    }

    function testCancelRelease_fromEscrowFailsIfTransactionNotSubmittedForRelease()
    {
        $transaction = $this->createEscrowedTransaction();
        $result = Transaction::cancelRelease($transaction->id);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('base');
        $this->assertEquals(
            Codes::TRANSACTION_CANNOT_CANCEL_RELEASE,
            $errors[0]->code
        );
    }

    function testCreateFromTransparentRedirect()
    {
        TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createTransactionViaTr(
            array(
                'transaction' => array(
                    'customer'    => array(
                        'first_name' => 'First'
                    ),
                    'credit_card' => array(
                        'number'          => '5105105105105100',
                        'expiration_date' => '05/12'
                    )
                )
            ),
            array(
                'transaction' => array(
                    'type'   => Transaction::SALE,
                    'amount' => '100.00'
                )
            )
        );
        $result = Transaction::createFromTransparentRedirect($queryString);
        TestHelper::assertPrintable($result);
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Transaction::SALE, $result->transaction->type);
        $this->assertEquals(Transaction::AUTHORIZED, $result->transaction->status);
        $creditCard = $result->transaction->creditCardDetails;
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('US', $creditCard->customerLocation);
        $this->assertEquals('MasterCard', $creditCard->cardType);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('510510******5100', $creditCard->maskedNumber);
        $customer = $result->transaction->customerDetails;
        $this->assertequals('First', $customer->firstName);
    }

    function testCreateFromTransparentRedirectWithInvalidParams()
    {
        TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createTransactionViaTr(
            array(
                'transaction' => array(
                    'bad_key'     => 'bad_value',
                    'customer'    => array(
                        'first_name' => 'First'
                    ),
                    'credit_card' => array(
                        'number'          => '5105105105105100',
                        'expiration_date' => '05/12'
                    )
                )
            ),
            array(
                'transaction' => array(
                    'type'   => Transaction::SALE,
                    'amount' => '100.00'
                )
            )
        );
        try {
            $result = Transaction::createFromTransparentRedirect($queryString);
            $this->fail();
        } catch (Authorization $e) {
            $this->assertEquals("Invalid params: transaction[bad_key]", $e->getMessage());
        }
    }

    function testCreateFromTransparentRedirect_withParamsInTrData()
    {
        TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createTransactionViaTr(
            array(),
            array(
                'transaction' => array(
                    'type'       => Transaction::SALE,
                    'amount'     => '100.00',
                    'customer'   => array(
                        'firstName' => 'First'
                    ),
                    'creditCard' => array(
                        'number'         => '5105105105105100',
                        'expirationDate' => '05/12'
                    )
                )
            )
        );
        $result = Transaction::createFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Transaction::SALE, $result->transaction->type);
        $this->assertEquals(Transaction::AUTHORIZED, $result->transaction->status);
        $creditCard = $result->transaction->creditCardDetails;
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('US', $creditCard->customerLocation);
        $this->assertEquals('MasterCard', $creditCard->cardType);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('510510******5100', $creditCard->maskedNumber);
        $customer = $result->transaction->customerDetails;
        $this->assertequals('First', $customer->firstName);
    }

    function testCreateFromTransparentRedirect_withValidationErrors()
    {
        TestHelper::suppressDeprecationWarnings();
        $queryString = $this->createTransactionViaTr(
            array(
                'transaction' => array(
                    'customer'    => array(
                        'first_name' => str_repeat('x', 256),
                    ),
                    'credit_card' => array(
                        'number'          => 'invalid',
                        'expiration_date' => ''
                    )
                )
            ),
            array(
                'transaction' => array('type' => Transaction::SALE)
            )
        );
        $result = Transaction::createFromTransparentRedirect($queryString);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->forKey('customer')->onAttribute('firstName');
        $this->assertEquals(Codes::CUSTOMER_FIRST_NAME_IS_TOO_LONG, $errors[0]->code);
        $errors = $result->errors->forKey('transaction')->forKey('creditCard')->onAttribute('number');
        $this->assertTrue(count($errors) > 0);
        $errors = $result->errors->forKey('transaction')->forKey('creditCard')->onAttribute('expirationDate');
        $this->assertEquals(Codes::CREDIT_CARD_EXPIRATION_DATE_IS_REQUIRED, $errors[0]->code);
    }

    function testRefund()
    {
        $transaction = $this->createTransactionToRefund();
        $result = Transaction::refund($transaction->id);
        $this->assertTrue($result->success);
        $refund = $result->transaction;
        $this->assertEquals(Transaction::CREDIT, $refund->type);
        $this->assertEquals($transaction->id, $refund->refundedTransactionId);
        $this->assertEquals($refund->id, Transaction::find($transaction->id)->refundId);
    }

    function testRefundWithPartialAmount()
    {
        $transaction = $this->createTransactionToRefund();
        $result = Transaction::refund($transaction->id, '50.00');
        $this->assertTrue($result->success);
        $this->assertEquals(Transaction::CREDIT, $result->transaction->type);
        $this->assertEquals("50.00", $result->transaction->amount);
    }

    function testMultipleRefundsWithPartialAmounts()
    {
        $transaction = $this->createTransactionToRefund();

        $transaction1 = Transaction::refund($transaction->id, '50.00')->transaction;
        $this->assertEquals(Transaction::CREDIT, $transaction1->type);
        $this->assertEquals("50.00", $transaction1->amount);

        $transaction2 = Transaction::refund($transaction->id, '50.00')->transaction;
        $this->assertEquals(Transaction::CREDIT, $transaction2->type);
        $this->assertEquals("50.00", $transaction2->amount);

        $transaction = Transaction::find($transaction->id);

        $expectedRefundIds = array($transaction1->id, $transaction2->id);
        $refundIds = $transaction->refundIds;
        sort($expectedRefundIds);
        sort($refundIds);

        $this->assertEquals($expectedRefundIds, $refundIds);
    }

    function testRefundWithUnsuccessfulPartialAmount()
    {
        $transaction = $this->createTransactionToRefund();
        $result = Transaction::refund($transaction->id, '150.00');
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->onAttribute('amount');
        $this->assertEquals(
            Codes::TRANSACTION_REFUND_AMOUNT_IS_TOO_LARGE,
            $errors[0]->code
        );
    }

    function testGatewayRejectionOnAvs()
    {
        $old_merchant_id = Configuration::merchantId();
        $old_public_key = Configuration::publicKey();
        $old_private_key = Configuration::privateKey();

        Configuration::merchantId('processing_rules_merchant_id');
        Configuration::publicKey('processing_rules_public_key');
        Configuration::privateKey('processing_rules_private_key');

        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'billing'    => array(
                'streetAddress' => '200 2nd Street'
            ),
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));

        Configuration::merchantId($old_merchant_id);
        Configuration::publicKey($old_public_key);
        Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        TestHelper::assertPrintable($result);
        $transaction = $result->transaction;

        $this->assertEquals(Transaction::AVS, $transaction->gatewayRejectionReason);
    }

    function testGatewayRejectionOnAvsAndCvv()
    {
        $old_merchant_id = Configuration::merchantId();
        $old_public_key = Configuration::publicKey();
        $old_private_key = Configuration::privateKey();

        Configuration::merchantId('processing_rules_merchant_id');
        Configuration::publicKey('processing_rules_public_key');
        Configuration::privateKey('processing_rules_private_key');

        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'billing'    => array(
                'postalCode' => '20000'
            ),
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
                'cvv'            => '200'
            )
        ));

        Configuration::merchantId($old_merchant_id);
        Configuration::publicKey($old_public_key);
        Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $this->assertEquals(Transaction::AVS_AND_CVV, $transaction->gatewayRejectionReason);
    }

    function testGatewayRejectionOnCvv()
    {
        $old_merchant_id = Configuration::merchantId();
        $old_public_key = Configuration::publicKey();
        $old_private_key = Configuration::privateKey();

        Configuration::merchantId('processing_rules_merchant_id');
        Configuration::publicKey('processing_rules_public_key');
        Configuration::privateKey('processing_rules_private_key');

        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
                'cvv'            => '200'
            )
        ));

        Configuration::merchantId($old_merchant_id);
        Configuration::publicKey($old_public_key);
        Configuration::privateKey($old_private_key);

        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $this->assertEquals(Transaction::CVV, $transaction->gatewayRejectionReason);
    }

    function testGatewayRejectionOnFraud()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '4000111111111511',
                'expirationDate' => '05/17',
                'cvv'            => '333'
            )
        ));

        $this->assertFalse($result->success);
        $this->assertEquals(Transaction::FRAUD, $result->transaction->gatewayRejectionReason);
    }

    function testSnapshotPlanIdAddOnsAndDiscountsFromSubscription()
    {
        $creditCard = SubscriptionTestHelper::createCreditCard();
        $plan = SubscriptionTestHelper::triallessPlan();
        $result = Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId'             => $plan['id'],
            'addOns'             => array(
                'add' => array(
                    array(
                        'amount'                => '11.00',
                        'inheritedFromId'       => 'increase_10',
                        'quantity'              => 2,
                        'numberOfBillingCycles' => 5
                    ),
                    array(
                        'amount'                => '21.00',
                        'inheritedFromId'       => 'increase_20',
                        'quantity'              => 3,
                        'numberOfBillingCycles' => 6
                    )
                ),
            ),
            'discounts'          => array(
                'add' => array(
                    array(
                        'amount'          => '7.50',
                        'inheritedFromId' => 'discount_7',
                        'quantity'        => 2,
                        'neverExpires'    => true
                    )
                )
            )
        ));

        $transaction = $result->subscription->transactions[0];

        $this->assertEquals($transaction->planId, $plan['id']);

        $addOns = $transaction->addOns;
        SubscriptionTestHelper::sortModificationsById($addOns);

        $this->assertEquals($addOns[0]->amount, "11.00");
        $this->assertEquals($addOns[0]->id, "increase_10");
        $this->assertEquals($addOns[0]->quantity, 2);
        $this->assertEquals($addOns[0]->numberOfBillingCycles, 5);
        $this->assertFalse($addOns[0]->neverExpires);

        $this->assertEquals($addOns[1]->amount, "21.00");
        $this->assertEquals($addOns[1]->id, "increase_20");
        $this->assertEquals($addOns[1]->quantity, 3);
        $this->assertEquals($addOns[1]->numberOfBillingCycles, 6);
        $this->assertFalse($addOns[1]->neverExpires);

        $discounts = $transaction->discounts;
        $this->assertEquals($discounts[0]->amount, "7.50");
        $this->assertEquals($discounts[0]->id, "discount_7");
        $this->assertEquals($discounts[0]->quantity, 2);
        $this->assertEquals($discounts[0]->numberOfBillingCycles, null);
        $this->assertTrue($discounts[0]->neverExpires);
    }

    function createTransactionViaTr($regularParams, $trParams)
    {
        TestHelper::suppressDeprecationWarnings();
        $trData = TransparentRedirect::transactionData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );
        return TestHelper::submitTrRequest(
            Transaction::createTransactionUrl(),
            $regularParams,
            $trData
        );
    }

    function createTransactionToRefund()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'options'    => array('submitForSettlement' => true)
        ));
        TestHelper::settle($transaction->id);
        return $transaction;
    }

    function createEscrowedTransaction()
    {
        $result = Transaction::sale(array(
            'merchantAccountId' => TestHelper::nonDefaultSubMerchantAccountId(),
            'amount'            => '100.00',
            'creditCard'        => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            ),
            'options'           => array(
                'holdInEscrow' => true
            ),
            'serviceFeeAmount'  => '1.00'
        ));
        TestHelper::escrow($result->transaction->id);
        return $result->transaction;
    }

    function testCardTypeIndicators()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => CreditCardNumbers_CardTypeIndicators::PREPAID,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals(CreditCard::PREPAID_YES, $transaction->creditCardDetails->prepaid);

        $prepaid_card_transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => CreditCardNumbers_CardTypeIndicators::COMMERCIAL,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals(CreditCard::COMMERCIAL_YES, $prepaid_card_transaction->creditCardDetails->commercial);

        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => CreditCardNumbers_CardTypeIndicators::PAYROLL,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals(CreditCard::PAYROLL_YES, $transaction->creditCardDetails->payroll);

        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => CreditCardNumbers_CardTypeIndicators::HEALTHCARE,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals(CreditCard::HEALTHCARE_YES, $transaction->creditCardDetails->healthcare);

        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => CreditCardNumbers_CardTypeIndicators::DURBIN_REGULATED,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals(CreditCard::DURBIN_REGULATED_YES, $transaction->creditCardDetails->durbinRegulated);

        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => CreditCardNumbers_CardTypeIndicators::DEBIT,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals(CreditCard::DEBIT_YES, $transaction->creditCardDetails->debit);

        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => CreditCardNumbers_CardTypeIndicators::ISSUING_BANK,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals("NETWORK ONLY", $transaction->creditCardDetails->issuingBank);

        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => CreditCardNumbers_CardTypeIndicators::COUNTRY_OF_ISSUANCE,
                'expirationDate' => '05/12',
            )
        ));

        $this->assertEquals("USA", $transaction->creditCardDetails->countryOfIssuance);
    }


    function testCreate_withVaultedPayPal()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $customer = Customer::createNoValidate();
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        PaymentMethod::create(array(
            'customerId'         => $customer->id,
            'paymentMethodNonce' => $nonce
        ));
        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodToken' => $paymentMethodToken,
        ));
        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
    }

    function testCreate_withFuturePayPal()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->setExpectedException('Exception_NotFound');
        PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withPayeeEmail()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'paypalAccount'      => array(
                'payeeEmail' => 'payee@example.com'
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->assertNotNull($transaction->paypalDetails->payeeEmail);
        $this->setExpectedException('Exception_NotFound');
        PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withPayeeEmailInOptions()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'paypalAccount'      => array(),
            'options'            => array(
                'payeeEmail' => 'payee@example.com'
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->assertNotNull($transaction->paypalDetails->payeeEmail);
        $this->setExpectedException('Exception_NotFound');
        PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withPayeeEmailInOptionsPayPal()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'paypalAccount'      => array(),
            'options'            => array(
                'paypal' => array(
                    'payeeEmail' => 'payee@example.com'
                )
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->assertNotNull($transaction->paypalDetails->payeeEmail);
        $this->setExpectedException('Exception_NotFound');
        PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withPayPalCustomField()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'paypalAccount'      => array(),
            'options'            => array(
                'paypal' => array(
                    'customField' => 'custom field stuff'
                )
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('custom field stuff', $transaction->paypalDetails->customField);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->setExpectedException('Exception_NotFound');
        PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withPayPalReturnsPaymentInstrumentType()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(PaymentInstrumentType::PAYPAL_ACCOUNT, $transaction->paymentInstrumentType);
        $this->assertNotNull($transaction->paypalDetails->debugId);
    }

    function testCreate_withFuturePayPalAndVault()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'consent_code' => 'PAYPAL_CONSENT_CODE',
                'token'        => $paymentMethodToken
            )
        ));

        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options'            => array(
                'storeInVault' => true
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $foundPayPalAccount = PaymentMethod::find($paymentMethodToken);
        $this->assertEquals($paymentMethodToken, $foundPayPalAccount->token);
    }

    function testCreate_withOnetimePayPal()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'access_token' => 'PAYPAL_ACCESS_TOKEN',
                'token'        => $paymentMethodToken
            )
        ));

        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->setExpectedException('Exception_NotFound');
        PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withOnetimePayPalAndDoesNotVault()
    {
        $paymentMethodToken = 'PAYPAL_TOKEN-' . strval(rand());
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'access_token' => 'PAYPAL_ACCESS_TOKEN',
                'token'        => $paymentMethodToken
            )
        ));

        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options'            => array(
                'storeInVault' => true
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('payer@example.com', $transaction->paypalDetails->payerEmail);
        $this->assertNotNull($transaction->paypalDetails->imageUrl);
        $this->assertNotNull($transaction->paypalDetails->debugId);
        $this->setExpectedException('Exception_NotFound');
        PaymentMethod::find($paymentMethodToken);
    }

    function testCreate_withPayPalAndSubmitForSettlement()
    {
        $nonce = Nonces::$paypalOneTimePayment;
        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options'            => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Transaction::SETTLING, $transaction->status);
    }

    function testCreate_withPayPalHandlesBadUnvalidatedNonces()
    {
        $http = new HttpClientApi(Configuration::$global);
        $nonce = $http->nonceForPayPalAccount(array(
            'paypal_account' => array(
                'access_token' => 'PAYPAL_ACCESS_TOKEN',
                'consent_code' => 'PAYPAL_CONSENT_CODE'
            )
        ));

        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options'            => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->forKey('paypalAccount')->errors;
        $this->assertEquals(Codes::PAYPAL_ACCOUNT_CANNOT_HAVE_BOTH_ACCESS_TOKEN_AND_CONSENT_CODE,
            $errors[0]->code);
    }

    function testCreate_withPayPalHandlesNonExistentNonces()
    {
        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => 'NON_EXISTENT_NONCE',
            'options'            => array(
                'submitForSettlement' => true
            )
        ));
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->errors;
        $this->assertEquals(Codes::TRANSACTION_PAYMENT_METHOD_NONCE_UNKNOWN, $errors[0]->code);
    }

    function testVoid_withPayPal()
    {
        $nonce = Nonces::$paypalOneTimePayment;

        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce
        ));

        $this->assertTrue($result->success);
        $voided_transaction = Transaction::voidNoValidate($result->transaction->id);
        $this->assertEquals(Transaction::VOIDED, $voided_transaction->status);
    }

    function testVoid_failsOnDeclinedPayPal()
    {
        $nonce = Nonces::$paypalOneTimePayment;

        $result = Transaction::sale(array(
            'amount'             => TransactionAmounts::$decline,
            'paymentMethodNonce' => $nonce
        ));
        $this->setExpectedException('Exception_ValidationsFailed');
        Transaction::voidNoValidate($result->transaction->id);
    }

    function testRefund_withPayPal()
    {
        $nonce = Nonces::$paypalOneTimePayment;

        $transactionResult = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options'            => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($transactionResult->success);
        TestHelper::settle($transactionResult->transaction->id);

        $result = Transaction::refund($transactionResult->transaction->id);
        $this->assertTrue($result->success);
        $this->assertEquals($result->transaction->type, Transaction::CREDIT);
    }

    function testRefund_withPayPalAssignsRefundId()
    {
        $nonce = Nonces::$paypalOneTimePayment;

        $transactionResult = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options'            => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($transactionResult->success);
        $originalTransaction = $transactionResult->transaction;
        TestHelper::settle($transactionResult->transaction->id);

        $result = Transaction::refund($transactionResult->transaction->id);
        $this->assertTrue($result->success);
        $refundTransaction = $result->transaction;
        $updatedOriginalTransaction = Transaction::find($originalTransaction->id);
        $this->assertEquals($refundTransaction->id, $updatedOriginalTransaction->refundId);
    }

    function testRefund_withPayPalAssignsRefundedTransactionId()
    {
        $nonce = Nonces::$paypalOneTimePayment;

        $transactionResult = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options'            => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($transactionResult->success);
        $originalTransaction = $transactionResult->transaction;
        TestHelper::settle($transactionResult->transaction->id);

        $result = Transaction::refund($transactionResult->transaction->id);
        $this->assertTrue($result->success);
        $refundTransaction = $result->transaction;
        $this->assertEquals($refundTransaction->refundedTransactionId, $originalTransaction->id);
    }

    function testRefund_withPayPalFailsifAlreadyRefunded()
    {
        $nonce = Nonces::$paypalOneTimePayment;

        $transactionResult = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options'            => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($transactionResult->success);
        TestHelper::settle($transactionResult->transaction->id);

        $firstRefund = Transaction::refund($transactionResult->transaction->id);
        $this->assertTrue($firstRefund->success);
        $secondRefund = Transaction::refund($transactionResult->transaction->id);
        $this->assertFalse($secondRefund->success);
        $errors = $secondRefund->errors->forKey('transaction')->errors;
        $this->assertEquals(Codes::TRANSACTION_HAS_ALREADY_BEEN_REFUNDED, $errors[0]->code);
    }

    function testRefund_withPayPalFailsIfNotSettled()
    {
        $nonce = Nonces::$paypalOneTimePayment;

        $transactionResult = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
        ));

        $this->assertTrue($transactionResult->success);

        $result = Transaction::refund($transactionResult->transaction->id);
        $this->assertFalse($result->success);
        $errors = $result->errors->forKey('transaction')->errors;
        $this->assertEquals(Codes::TRANSACTION_CANNOT_REFUND_UNLESS_SETTLED, $errors[0]->code);
    }

    function testRefund_partialWithPayPal()
    {
        $nonce = Nonces::$paypalOneTimePayment;

        $transactionResult = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options'            => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($transactionResult->success);
        TestHelper::settle($transactionResult->transaction->id);

        $result = Transaction::refund(
            $transactionResult->transaction->id,
            $transactionResult->transaction->amount / 2
        );

        $this->assertTrue($result->success);
        $this->assertEquals($result->transaction->type, Transaction::CREDIT);
        $this->assertEquals($result->transaction->amount, $transactionResult->transaction->amount / 2);
    }

    function testRefund_multiplePartialWithPayPal()
    {
        $nonce = Nonces::$paypalOneTimePayment;

        $transactionResult = Transaction::sale(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodNonce' => $nonce,
            'options'            => array(
                'submitForSettlement' => true
            )
        ));

        $this->assertTrue($transactionResult->success);
        $originalTransaction = $transactionResult->transaction;
        TestHelper::settle($originalTransaction->id);

        $firstRefund = Transaction::refund(
            $transactionResult->transaction->id,
            $transactionResult->transaction->amount / 2
        );
        $this->assertTrue($firstRefund->success);
        $firstRefundTransaction = $firstRefund->transaction;

        $secondRefund = Transaction::refund(
            $transactionResult->transaction->id,
            $transactionResult->transaction->amount / 2
        );
        $this->assertTrue($secondRefund->success);
        $secondRefundTransaction = $secondRefund->transaction;


        $updatedOriginalTransaction = Transaction::find($originalTransaction->id);
        $expectedRefundIds = array($secondRefundTransaction->id, $firstRefundTransaction->id);

        $updatedRefundIds = $updatedOriginalTransaction->refundIds;

        $this->assertTrue(in_array($expectedRefundIds[0], $updatedRefundIds));
        $this->assertTrue(in_array($expectedRefundIds[1], $updatedRefundIds));
    }

    function testIncludeProcessorSettlementResponseForSettlementDeclinedTransaction()
    {
        $result = Transaction::sale(array(
            "paymentMethodNonce" => Nonces::$paypalFuturePayment,
            "amount"             => "100",
            "options"            => array(
                "submitForSettlement" => true
            )
        ));

        $this->assertTrue($result->success);

        $transaction = $result->transaction;
        TestHelper::settlementDecline($transaction->id);

        $inline_transaction = Transaction::find($transaction->id);
        $this->assertEquals($inline_transaction->status, Transaction::SETTLEMENT_DECLINED);
        $this->assertEquals($inline_transaction->processorSettlementResponseCode, "4001");
        $this->assertEquals($inline_transaction->processorSettlementResponseText, "Settlement Declined");
    }

    function testIncludeProcessorSettlementResponseForSettlementPendingTransaction()
    {
        $result = Transaction::sale(array(
            "paymentMethodNonce" => Nonces::$paypalFuturePayment,
            "amount"             => "100",
            "options"            => array(
                "submitForSettlement" => true
            )
        ));

        $this->assertTrue($result->success);

        $transaction = $result->transaction;
        TestHelper::settlementPending($transaction->id);

        $inline_transaction = Transaction::find($transaction->id);
        $this->assertEquals($inline_transaction->status, Transaction::SETTLEMENT_PENDING);
        $this->assertEquals($inline_transaction->processorSettlementResponseCode, "4002");
        $this->assertEquals($inline_transaction->processorSettlementResponseText, "Settlement Pending");
    }

    function testSale_withLodgingIndustryData()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'industry'   => array(
                'industryType' => Transaction::LODGING_INDUSTRY,
                'data'         => array(
                    'folioNumber'  => 'aaa',
                    'checkInDate'  => '2014-07-07',
                    'checkOutDate' => '2014-07-09',
                    'roomRate'     => '239.00'
                )
            )
        ));
        $this->assertTrue($result->success);
    }

    function testSale_withLodgingIndustryDataValidation()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'industry'   => array(
                'industryType' => Transaction::LODGING_INDUSTRY,
                'data'         => array(
                    'folioNumber'  => 'aaa',
                    'checkInDate'  => '2014-07-07',
                    'checkOutDate' => '2014-06-09',
                    'roomRate'     => '239.00'
                )
            )
        ));
        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $errors = $result->errors->forKey('transaction')->forKey('industry')->onAttribute('checkOutDate');
        $this->assertEquals(Codes::INDUSTRY_DATA_LODGING_CHECK_OUT_DATE_MUST_FOLLOW_CHECK_IN_DATE,
            $errors[0]->code);
    }

    function testSale_withTravelCruiseIndustryData()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'industry'   => array(
                'industryType' => Transaction::TRAVEL_AND_CRUISE_INDUSTRY,
                'data'         => array(
                    'travelPackage'       => 'flight',
                    'departureDate'       => '2014-07-07',
                    'lodgingCheckInDate'  => '2014-07-09',
                    'lodgingCheckOutDate' => '2014-07-10',
                    'lodgingName'         => 'Disney',
                )
            )
        ));
        $this->assertTrue($result->success);
    }

    function testSale_withTravelCruiseIndustryDataValidation()
    {
        $result = Transaction::sale(array(
            'amount'     => '100.00',
            'creditCard' => array(
                'number'         => '5105105105105100',
                'expirationDate' => '05/12',
            ),
            'industry'   => array(
                'industryType' => Transaction::TRAVEL_AND_CRUISE_INDUSTRY,
                'data'         => array(
                    'travelPackage'       => 'invalid',
                    'departureDate'       => '2014-07-07',
                    'lodgingCheckInDate'  => '2014-07-09',
                    'lodgingCheckOutDate' => '2014-07-10',
                    'lodgingName'         => 'Disney',
                )
            )
        ));
        $this->assertFalse($result->success);
        $transaction = $result->transaction;

        $errors = $result->errors->forKey('transaction')->forKey('industry')->onAttribute('travelPackage');
        $this->assertEquals(Codes::INDUSTRY_DATA_TRAVEL_CRUISE_TRAVEL_PACKAGE_IS_INVALID, $errors[0]->code);
    }
}
