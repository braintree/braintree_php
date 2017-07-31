<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class MerchantAccountTest extends Setup
{
    private static $deprecatedValidParams = [
      'applicantDetails' => [
        'companyName' => "Robot City",
        'firstName' => "Joe",
        'lastName' => "Bloggs",
        'email' => "joe@bloggs.com",
        'phone' => "555-555-5555",
        'address' => [
          'streetAddress' => "123 Credibility St.",
          'postalCode' => "60606",
          'locality' => "Chicago",
          'region' => "IL",
        ],
        'dateOfBirth' => "10/9/1980",
        'ssn' => "123-00-1234",
        'taxId' => "123456789",
        'routingNumber' => "122100024",
        'accountNumber' => "43759348798"
      ],
      'tosAccepted' => true,
      'masterMerchantAccountId' => "sandbox_master_merchant_account"
    ];

    private static $validParams = [
      'individual' => [
        'firstName' => "Joe",
        'lastName' => "Bloggs",
        'email' => "joe@bloggs.com",
        'phone' => "555-555-5555",
        'address' => [
          'streetAddress' => "123 Credibility St.",
          'postalCode' => "60606",
          'locality' => "Chicago",
          'region' => "IL",
        ],
        'dateOfBirth' => "10/9/1980",
        'ssn' => "123-00-1234",
      ],
      'business' => [
        'dbaName' => "Robot City",
        'legalName' => "Robot City INC",
        'taxId' => "123456789",
      ],
      'funding' => [
        'routingNumber' => "122100024",
        'accountNumber' => "43759348798",
        'destination' => Braintree\MerchantAccount::FUNDING_DESTINATION_BANK,
        'descriptor' => 'Joes Bloggs MI',
      ],
      'tosAccepted' => true,
      'masterMerchantAccountId' => "sandbox_master_merchant_account"
    ];

    public function testCreate()
    {
        $result = Braintree\MerchantAccount::create(self::$validParams);
        $this->assertEquals(true, $result->success);
        $merchantAccount = $result->merchantAccount;
        $this->assertEquals(Braintree\MerchantAccount::STATUS_PENDING, $merchantAccount->status);
        $this->assertEquals("sandbox_master_merchant_account", $merchantAccount->masterMerchantAccount->id);
    }

    public function testGatewayCreate()
    {
        $gateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ]);
        $result = $gateway->merchantAccount()->create(self::$validParams);
        $this->assertEquals(true, $result->success);
        $merchantAccount = $result->merchantAccount;
        $this->assertEquals(Braintree\MerchantAccount::STATUS_PENDING, $merchantAccount->status);
        $this->assertEquals("sandbox_master_merchant_account", $merchantAccount->masterMerchantAccount->id);
    }

    public function testCreateWithDeprecatedParameters()
    {
        Test\Helper::suppressDeprecationWarnings();
        $result = Braintree\MerchantAccount::create(self::$deprecatedValidParams);
        $this->assertEquals(true, $result->success);
        $merchantAccount = $result->merchantAccount;
        $this->assertEquals(Braintree\MerchantAccount::STATUS_PENDING, $merchantAccount->status);
        $this->assertEquals("sandbox_master_merchant_account", $merchantAccount->masterMerchantAccount->id);
    }

    public function testCreateWithId()
    {
        $rand = rand(1, 1000);
        $subMerchantAccountId = "sub_merchant_account_id" + $rand;
        $validParamsWithId = array_merge([], self::$validParams);
        $validParamsWithId['id'] = $subMerchantAccountId;
        $result = Braintree\MerchantAccount::create($validParamsWithId);
        $this->assertEquals(true, $result->success);
        $merchantAccount = $result->merchantAccount;
        $this->assertEquals(Braintree\MerchantAccount::STATUS_PENDING, $merchantAccount->status);
        $this->assertEquals("sandbox_master_merchant_account", $merchantAccount->masterMerchantAccount->id);
        $this->assertEquals("sub_merchant_account_id" + $rand, $merchantAccount->id);
    }

    public function testFailedCreate()
    {
        $result = Braintree\MerchantAccount::create([]);
        $this->assertEquals(false, $result->success);
        $errors = $result->errors->forKey('merchantAccount')->onAttribute('masterMerchantAccountId');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_ACCOUNT_MASTER_MERCHANT_ACCOUNT_ID_IS_REQUIRED, $errors[0]->code);
    }

    public function testCreateWithFundingDestination()
    {
        $params = array_merge([], self::$validParams);
        $params['funding']['destination'] = Braintree\MerchantAccount::FUNDING_DESTINATION_BANK;
        $result = Braintree\MerchantAccount::create($params);
        $this->assertEquals(true, $result->success);

        $params = array_merge([], self::$validParams);
        $params['funding']['destination'] = Braintree\MerchantAccount::FUNDING_DESTINATION_EMAIL;
        $params['funding']['email'] = "billgates@outlook.com";
        $result = Braintree\MerchantAccount::create($params);
        $this->assertEquals(true, $result->success);

        $params = array_merge([], self::$validParams);
        $params['funding']['destination'] = Braintree\MerchantAccount::FUNDING_DESTINATION_MOBILE_PHONE;
        $params['funding']['mobilePhone'] = "1112224444";
        $result = Braintree\MerchantAccount::create($params);
        $this->assertEquals(true, $result->success);
    }

    public function testFind()
    {
        $params = array_merge([], self::$validParams);
        $result = Braintree\MerchantAccount::create(self::$validParams);
        $this->assertEquals(true, $result->success);
        $this->assertEquals(Braintree\MerchantAccount::STATUS_PENDING, $result->merchantAccount->status);

        $id = $result->merchantAccount->id;
        $merchantAccount = Braintree\MerchantAccount::find($id);

        $this->assertEquals(Braintree\MerchantAccount::STATUS_ACTIVE, $merchantAccount->status);
        $this->assertEquals($params['individual']['firstName'], $merchantAccount->individualDetails->firstName);
        $this->assertEquals($params['individual']['lastName'], $merchantAccount->individualDetails->lastName);
    }

    public function testRetrievesMasterMerchantAccountCurrencyIsoCode()
    {
        $merchantAccount = Braintree\MerchantAccount::find("sandbox_master_merchant_account");

        $this->assertEquals("USD", $merchantAccount->currencyIsoCode);
    }

    public function testFind_throwsIfNotFound()
    {
        $this->setExpectedException('Braintree\Exception\NotFound', 'merchant account with id does-not-exist not found');
        Braintree\MerchantAccount::find('does-not-exist');
    }

    public function testUpdate()
    {
        $params = array_merge([], self::$validParams);
        unset($params["tosAccepted"]);
        unset($params["masterMerchantAccountId"]);
        $params["individual"]["firstName"] = "John";
        $params["individual"]["lastName"] = "Doe";
        $params["individual"]["email"] = "john.doe@example.com";
        $params["individual"]["dateOfBirth"] = "1970-01-01";
        $params["individual"]["phone"] = "3125551234";
        $params["individual"]["address"]["streetAddress"] = "123 Fake St";
        $params["individual"]["address"]["locality"] = "Chicago";
        $params["individual"]["address"]["region"] = "IL";
        $params["individual"]["address"]["postalCode"] = "60622";
        $params["business"]["dbaName"] = "James's Bloggs";
        $params["business"]["legalName"] = "James's Bloggs Inc";
        $params["business"]["taxId"] = "123456789";
        $params["business"]["address"]["streetAddress"] = "999 Fake St";
        $params["business"]["address"]["locality"] = "Miami";
        $params["business"]["address"]["region"] = "FL";
        $params["business"]["address"]["postalCode"] = "99999";
        $params["funding"]["accountNumber"] = "43759348798";
        $params["funding"]["routingNumber"] = "071000013";
        $params["funding"]["email"] = "check@this.com";
        $params["funding"]["mobilePhone"] = "1234567890";
        $params["funding"]["destination"] = Braintree\MerchantAccount::FUNDING_DESTINATION_BANK;
        $params["funding"]["descriptor"] = "Joes Bloggs FL";

        $result = Braintree\MerchantAccount::update("sandbox_sub_merchant_account", $params);
        $this->assertEquals(true, $result->success);

        $updatedMerchantAccount = $result->merchantAccount;
        $this->assertEquals("active", $updatedMerchantAccount->status);
        $this->assertEquals("sandbox_sub_merchant_account", $updatedMerchantAccount->id);
        $this->assertEquals("sandbox_master_merchant_account", $updatedMerchantAccount->masterMerchantAccount->id);
        $this->assertEquals("John", $updatedMerchantAccount->individualDetails->firstName);
        $this->assertEquals("Doe", $updatedMerchantAccount->individualDetails->lastName);
        $this->assertEquals("john.doe@example.com", $updatedMerchantAccount->individualDetails->email);
        $this->assertEquals("1970-01-01", $updatedMerchantAccount->individualDetails->dateOfBirth);
        $this->assertEquals("3125551234", $updatedMerchantAccount->individualDetails->phone);
        $this->assertEquals("123 Fake St", $updatedMerchantAccount->individualDetails->addressDetails->streetAddress);
        $this->assertEquals("Chicago", $updatedMerchantAccount->individualDetails->addressDetails->locality);
        $this->assertEquals("IL", $updatedMerchantAccount->individualDetails->addressDetails->region);
        $this->assertEquals("60622", $updatedMerchantAccount->individualDetails->addressDetails->postalCode);
        $this->assertEquals("James's Bloggs", $updatedMerchantAccount->businessDetails->dbaName);
        $this->assertEquals("James's Bloggs Inc", $updatedMerchantAccount->businessDetails->legalName);
        $this->assertEquals("123456789", $updatedMerchantAccount->businessDetails->taxId);
        $this->assertEquals("999 Fake St", $updatedMerchantAccount->businessDetails->addressDetails->streetAddress);
        $this->assertEquals("Miami", $updatedMerchantAccount->businessDetails->addressDetails->locality);
        $this->assertEquals("FL", $updatedMerchantAccount->businessDetails->addressDetails->region);
        $this->assertEquals("99999", $updatedMerchantAccount->businessDetails->addressDetails->postalCode);
        $this->assertEquals("8798", $updatedMerchantAccount->fundingDetails->accountNumberLast4);
        $this->assertEquals("071000013", $updatedMerchantAccount->fundingDetails->routingNumber);
        $this->assertEquals("check@this.com", $updatedMerchantAccount->fundingDetails->email);
        $this->assertEquals("1234567890", $updatedMerchantAccount->fundingDetails->mobilePhone);
        $this->assertEquals(Braintree\MerchantAccount::FUNDING_DESTINATION_BANK, $updatedMerchantAccount->fundingDetails->destination);
        $this->assertEquals("Joes Bloggs FL", $updatedMerchantAccount->fundingDetails->descriptor);
    }

    public function testUpdateDoesNotRequireAllFields()
    {
        $params = [
            'individual' => [
                'firstName' => "Joe"
            ]
        ];
        $result = Braintree\MerchantAccount::update("sandbox_sub_merchant_account", $params);
        $this->assertEquals(true, $result->success);
    }

    public function testUpdateWithBlankFields()
    {
        $params = [
            'individual' => [
                'firstName' => "",
                'lastName' => "",
                'email' => "",
                'phone' => "",
                'address' => [
                    'streetAddress' => "",
                    'postalCode' => "",
                    'locality' => "",
                    'region' => "",
                ],
                'dateOfBirth' => "",
                'ssn' => "",
            ],
            'business' => [
                'dbaName' => "",
                'legalName' => "",
                'taxId' => "",
            ],
            'funding' => [
                'routingNumber' => "",
                'accountNumber' => "",
                'destination' => "",
            ],
        ];

        $result = Braintree\MerchantAccount::update("sandbox_sub_merchant_account", $params);
        $this->assertEquals(false, $result->success);

        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->onAttribute("firstName");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_FIRST_NAME_IS_REQUIRED);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->onAttribute("lastName");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_LAST_NAME_IS_REQUIRED);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->onAttribute("dateOfBirth");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_DATE_OF_BIRTH_IS_REQUIRED);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->onAttribute("email");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_EMAIL_IS_REQUIRED);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->forKey("address")->onAttribute("streetAddress");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_ADDRESS_STREET_ADDRESS_IS_REQUIRED);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->forKey("address")->onAttribute("postalCode");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_ADDRESS_POSTAL_CODE_IS_REQUIRED);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->forKey("address")->onAttribute("locality");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_ADDRESS_LOCALITY_IS_REQUIRED);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->forKey("address")->onAttribute("region");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_ADDRESS_REGION_IS_REQUIRED);
        $error = $result->errors->forKey("merchantAccount")->forKey("funding")->onAttribute("destination");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_FUNDING_DESTINATION_IS_REQUIRED);
    }

    public function testUpdateWithInvalidFields()
    {
        $params = [
          "individual" => [
            "firstName" => "<>",
            "lastName" => "<>",
            "email" => "bad",
            "phone" => "999",
            "address" => [
              "streetAddress" => "nope",
              "postalCode" => "1",
              "region" => "QQ",
            ],
            "dateOfBirth" => "hah",
            "ssn" => "12345",
          ],
          "business" => [
            "legalName" => "``{}",
            "dbaName" => "{}``",
            "taxId" => "bad",
            "address" => [
              "streetAddress" => "nope",
              "postalCode" => "1",
              "region" => "QQ",
            ],
          ],
          "funding" => [
            "destination" => "MY WALLET",
            "routingNumber" => "LEATHER",
            "accountNumber" => "BACK POCKET",
            "email" => "BILLFOLD",
            "mobilePhone" => "TRIFOLD"
          ],
        ];


        $result = Braintree\MerchantAccount::update("sandbox_sub_merchant_account", $params);
        $this->assertEquals(false, $result->success);

        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->onAttribute("firstName");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_FIRST_NAME_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->onAttribute("lastName");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_LAST_NAME_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->onAttribute("email");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_EMAIL_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->onAttribute("phone");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_PHONE_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->forKey("address")->onAttribute("streetAddress");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_ADDRESS_STREET_ADDRESS_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->forKey("address")->onAttribute("postalCode");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_ADDRESS_POSTAL_CODE_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->forKey("address")->onAttribute("region");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_ADDRESS_REGION_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("individual")->onAttribute("ssn");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_INDIVIDUAL_SSN_IS_INVALID);
        ;
        $error = $result->errors->forKey("merchantAccount")->forKey("business")->onAttribute("legalName");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_BUSINESS_LEGAL_NAME_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("business")->onAttribute("dbaName");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_BUSINESS_DBA_NAME_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("business")->onAttribute("taxId");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_BUSINESS_TAX_ID_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("business")->forKey("address")->onAttribute("streetAddress");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_BUSINESS_ADDRESS_STREET_ADDRESS_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("business")->forKey("address")->onAttribute("postalCode");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_BUSINESS_ADDRESS_POSTAL_CODE_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("business")->forKey("address")->onAttribute("region");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_BUSINESS_ADDRESS_REGION_IS_INVALID);

        $error = $result->errors->forKey("merchantAccount")->forKey("funding")->onAttribute("destination");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_FUNDING_DESTINATION_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("funding")->onAttribute("routingNumber");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_FUNDING_ROUTING_NUMBER_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("funding")->onAttribute("accountNumber");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_FUNDING_ACCOUNT_NUMBER_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("funding")->onAttribute("email");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_FUNDING_EMAIL_IS_INVALID);
        $error = $result->errors->forKey("merchantAccount")->forKey("funding")->onAttribute("mobilePhone");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_FUNDING_MOBILE_PHONE_IS_INVALID);
    }

    public function testUpdateWithInvalidBusinessFields()
    {
        $params = [
          "business" => [
            "legalName" => "",
            "taxId" => "111223333",
          ]
        ];

        $result = Braintree\MerchantAccount::update("sandbox_sub_merchant_account", $params);
        $this->assertEquals(false, $result->success);

        $error = $result->errors->forKey("merchantAccount")->forKey("business")->onAttribute("legalName");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_BUSINESS_LEGAL_NAME_IS_REQUIRED_WITH_TAX_ID);
        $error = $result->errors->forKey("merchantAccount")->forKey("business")->onAttribute("taxId");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_BUSINESS_TAX_ID_MUST_BE_BLANK);

        $params = [
          "business" => [
            "legalName" => "legal name",
            "taxId" => "",
          ]
        ];

        $result = Braintree\MerchantAccount::update("sandbox_sub_merchant_account", $params);
        $this->assertEquals(false, $result->success);

        $error = $result->errors->forKey("merchantAccount")->forKey("business")->onAttribute("taxId");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_BUSINESS_TAX_ID_IS_REQUIRED_WITH_LEGAL_NAME);
    }

    public function testUpdateWithInvalidFundingFields()
    {
        $params = [
          "funding" => [
            "destination" => Braintree\MerchantAccount::FUNDING_DESTINATION_EMAIL,
            "email" => "",
          ]
        ];

        $result = Braintree\MerchantAccount::update("sandbox_sub_merchant_account", $params);
        $this->assertEquals(false, $result->success);

        $error = $result->errors->forKey("merchantAccount")->forKey("funding")->onAttribute("email");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_FUNDING_EMAIL_IS_REQUIRED);

        $params = [
          "funding" => [
            "destination" => Braintree\MerchantAccount::FUNDING_DESTINATION_MOBILE_PHONE,
            "mobilePhone" => "",
          ]
        ];

        $result = Braintree\MerchantAccount::update("sandbox_sub_merchant_account", $params);
        $this->assertEquals(false, $result->success);

        $error = $result->errors->forKey("merchantAccount")->forKey("funding")->onAttribute("mobilePhone");
        $this->assertEquals($error[0]->code, Braintree\Error\Codes::MERCHANT_ACCOUNT_FUNDING_MOBILE_PHONE_IS_REQUIRED);
    }

    public function testCreateForCurrency()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$signup_client_id',
            'clientSecret' => 'client_secret$development$signup_client_secret',
        ]);
        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'GBR',
            'paymentMethods' => ['credit_card', 'paypal'],
        ]);

        $this->assertEquals(true, $result->success);

        $gateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);

        $result = $gateway->merchantAccount()->createForCurrency([
            'currency' => "USD",
        ]);

        $this->assertEquals(true, $result->success);

        $merchantAccount = $result->merchantAccount;
        $this->assertEquals("USD", $merchantAccount->currencyIsoCode);
        $this->assertEquals("USD", $merchantAccount->id);
    }

    public function testCreateForCurrencyWithDuplicateCurrency()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$signup_client_id',
            'clientSecret' => 'client_secret$development$signup_client_secret',
        ]);
        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'GBR',
            'paymentMethods' => ['credit_card', 'paypal'],
        ]);

        $this->assertEquals(true, $result->success);

        $gateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);

        $merchantAccount = $result->merchant->merchantAccounts[0];
        $result = $gateway->merchantAccount()->createForCurrency([
            'currency' => "GBP",
        ]);

        $this->assertEquals(false, $result->success);

        $errors = $result->errors->forKey('merchant')->onAttribute('currency');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_MERCHANT_ACCOUNT_EXISTS_FOR_CURRENCY, $errors[0]->code);
    }

    public function testCreateForCurrencyWithInvalidCurrency()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$signup_client_id',
            'clientSecret' => 'client_secret$development$signup_client_secret',
        ]);
        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'GBR',
            'paymentMethods' => ['credit_card', 'paypal'],
        ]);

        $this->assertEquals(true, $result->success);

        $gateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);

        $result = $gateway->merchantAccount()->createForCurrency([
            'currency' => "FAKE_CURRENCY",
        ]);

        $this->assertEquals(false, $result->success);

        $errors = $result->errors->forKey('merchant')->onAttribute('currency');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_CURRENCY_IS_INVALID, $errors[0]->code);
    }

    public function testCreateForCurrencyWithoutCurrency()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$signup_client_id',
            'clientSecret' => 'client_secret$development$signup_client_secret',
        ]);
        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'GBR',
            'paymentMethods' => ['credit_card', 'paypal'],
        ]);

        $this->assertEquals(true, $result->success);

        $gateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);

        $result = $gateway->merchantAccount()->createForCurrency([]);

        $this->assertEquals(false, $result->success);

        $errors = $result->errors->forKey('merchant')->onAttribute('currency');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_CURRENCY_IS_REQUIRED, $errors[0]->code);
    }

    public function testCreateForCurrencyWithDuplicateId()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$signup_client_id',
            'clientSecret' => 'client_secret$development$signup_client_secret',
        ]);
        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'GBR',
            'paymentMethods' => ['credit_card', 'paypal'],
        ]);

        $this->assertEquals(true, $result->success);

        $gateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);

        $merchantAccount = $result->merchant->merchantAccounts[0];
        $result = $gateway->merchantAccount()->createForCurrency([
            'currency' => "USD",
            'id' => $merchantAccount->id,
        ]);

        $this->assertEquals(false, $result->success);

        $errors = $result->errors->forKey('merchant')->onAttribute('id');
        $this->assertEquals(Braintree\Error\Codes::MERCHANT_MERCHANT_ACCOUNT_EXISTS_FOR_ID, $errors[0]->code);
    }

    public function testAllReturnsAllMerchantAccounts()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ]);

        $code = Test\Braintree\OAuthTestHelper::createGrant($gateway, [
            'merchant_public_id' => 'integration_merchant_id',
            'scope' => 'read_write'
        ]);

        $credentials = $gateway->oauth()->createTokenFromCode([
            'code' => $code,
        ]);

        $gateway = new Braintree\Gateway([
            'accessToken' => $credentials->accessToken
        ]);

        $result = $gateway->merchantAccount()->all();
        $merchantAccounts = [];
        foreach($result as $ma) {
            array_push($merchantAccounts, $ma);
        }
        $this->assertEquals(true, count($merchantAccounts) > 20);
    }

    public function testAllReturnsMerchantAccountWithCorrectAttributes()
    {
        $gateway = new Braintree\Gateway([
            'clientId' => 'client_id$development$integration_client_id',
            'clientSecret' => 'client_secret$development$integration_client_secret',
        ]);

        $result = $gateway->merchant()->create([
            'email' => 'name@email.com',
            'countryCodeAlpha3' => 'USA',
            'paymentMethods' => ['credit_card', 'paypal'],
        ]);

        $gateway = new Braintree\Gateway([
            'accessToken' => $result->credentials->accessToken,
        ]);

        $result = $gateway->merchantAccount()->all();
        $merchantAccounts = [];
        foreach($result as $ma) {
            array_push($merchantAccounts, $ma);
        }

        $this->assertEquals(1, count($merchantAccounts));
        $merchantAccount = $merchantAccounts[0];
        $this->assertEquals("USD", $merchantAccount->currencyIsoCode);
        $this->assertEquals(Braintree\MerchantAccount::STATUS_ACTIVE, $merchantAccount->status);
        $this->assertTrue($merchantAccount->default);
    }
}
