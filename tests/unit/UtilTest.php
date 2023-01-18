<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use stdClass;
use DateTime;
use Test\Setup;
use Braintree;

class UtilTest extends Setup
{
    public $success;
    public $transaction;

    public function testThrow401Exception()
    {
        $this->expectException('Braintree\Exception\Authentication');

        Braintree\Util::throwStatusCodeException(401);
    }

    public function testThrow403Exception()
    {
        $this->expectException('Braintree\Exception\Authorization');

        Braintree\Util::throwStatusCodeException(403);
    }

    public function testThrow404Exception()
    {
        $this->expectException('Braintree\Exception\NotFound');

        Braintree\Util::throwStatusCodeException(404);
    }

    public function testThrow408Exception()
    {
        $this->expectException('Braintree\Exception\RequestTimeout');

        Braintree\Util::throwStatusCodeException(408);
    }

    public function testThrow426Exception()
    {
        $this->expectException('Braintree\Exception\UpgradeRequired');

        Braintree\Util::throwStatusCodeException(426);
    }

    public function testThrow429Exception()
    {
        $this->expectException('Braintree\Exception\TooManyRequests');

        Braintree\Util::throwStatusCodeException(429);
    }

    public function testThrow500Exception()
    {
        $this->expectException('Braintree\Exception\ServerError');

        Braintree\Util::throwStatusCodeException(500);
    }

    public function testThrow504Exception()
    {
        $this->expectException('Braintree\Exception\GatewayTimeout');

        Braintree\Util::throwStatusCodeException(504);
    }

    public function testThrowUnknownException()
    {
        $this->expectException('Braintree\Exception\Unexpected');

        Braintree\Util::throwStatusCodeException(999);
    }

    public function testThrowGraphQLAuthenticationException()
    {
        $response = [
            "errors" => [
                [
                    "message" => "error_message",
                    "extensions" => [
                        "errorClass" => "AUTHENTICATION"
                    ]
                ]
            ]
        ];

        $this->expectException('Braintree\Exception\Authentication');

        Braintree\Util::throwGraphQLResponseException($response);
    }

    public function testThrowGraphQLAuthorizationException()
    {
        $response = [
            "errors" => [
                [
                    "message" => "error_message",
                    "extensions" => [
                        "errorClass" => "AUTHORIZATION"
                    ]
                ]
            ]
        ];

        $this->expectException('Braintree\Exception\Authorization');

        Braintree\Util::throwGraphQLResponseException($response);
    }

    public function testThrowGraphQLNotFoundException()
    {
        $response = [
            "errors" => [
                [
                    "message" => "error_message",
                    "extensions" => [
                        "errorClass" => "NOT_FOUND"
                    ]
                ]
            ]
        ];

        $this->expectException('Braintree\Exception\NotFound');

        Braintree\Util::throwGraphQLResponseException($response);
    }

    public function testThrowGraphQLUnsupportedClientException()
    {
        $response = [
            "errors" => [
                [
                    "message" => "error_message",
                    "extensions" => [
                        "errorClass" => "UNSUPPORTED_CLIENT"
                    ]
                ]
            ]
        ];

        $this->expectException('Braintree\Exception\UpgradeRequired');

        Braintree\Util::throwGraphQLResponseException($response);
    }

    public function testThrowGraphQLResourceLimitException()
    {
        $response = [
            "errors" => [
                [
                    "message" => "error_message",
                    "extensions" => [
                        "errorClass" => "RESOURCE_LIMIT"
                    ]
                ]
            ]
        ];

        $this->expectException('Braintree\Exception\TooManyRequests');

        Braintree\Util::throwGraphQLResponseException($response);
    }

    public function testThrowGraphQLInternalException()
    {
        $response = [
            "errors" => [
                [
                    "message" => "error_message",
                    "extensions" => [
                        "errorClass" => "INTERNAL"
                    ]
                ]
            ]
        ];

        $this->expectException('Braintree\Exception\ServerError');

        Braintree\Util::throwGraphQLResponseException($response);
    }

    public function testThrowGraphQLServiceAvailabilityException()
    {
        $response = [
            "errors" => [
                [
                    "message" => "error_message",
                    "extensions" => [
                        "errorClass" => "SERVICE_AVAILABILITY"
                    ]
                ]
            ]
        ];

        $this->expectException('Braintree\Exception\ServiceUnavailable');

        Braintree\Util::throwGraphQLResponseException($response);
    }

    public function testThrowGraphQLUnexpectedException()
    {
        $response = [
            "errors" => [
                [
                    "message" => "error_message",
                    "extensions" => [
                        "errorClass" => "UNDOCUMENTED_ERROR"
                    ]
                ]
            ]
        ];

        $this->expectException('Braintree\Exception\Unexpected');

        Braintree\Util::throwGraphQLResponseException($response);
    }

    public function testDoesNotThrowGraphQLValidationException()
    {
        $response = [
            "errors" => [
                [
                    "message" => "error_message",
                    "extensions" => [
                        "errorClass" => "VALIDATION"
                    ]
                ]
            ]
        ];
        $this->assertNull(Braintree\Util::throwGraphQLResponseException($response));
    }

    public function testThrowGraphQLUnexpectedExceptionAndNotValidationExceptionWhenBothArePresent()
    {
        $response = [
            "errors" => [
                [
                    "message" => "validation_error",
                    "extensions" => [
                        "errorClass" => "VALIDATION"
                    ]
                ],
                [
                    "message" => "error_message",
                    "extensions" => [
                        "errorClass" => "UNDOCUMENTED_ERROR"
                    ]
                ]
            ]
        ];

        $this->expectException('Braintree\Exception\Unexpected');

        Braintree\Util::throwGraphQLResponseException($response);
    }

    public function testExtractAttributeAsArrayReturnsEmptyArray()
    {
        $attributes = [];
        $this->assertEquals([], Braintree\Util::extractAttributeAsArray($attributes, "foo"));
    }

    public function testExtractAttributeAsArrayReturnsSingleElementArray()
    {
        $attributes = ['verification' => 'val1'];
        $this->assertEquals(['val1'], Braintree\Util::extractAttributeAsArray($attributes, "verification"));
    }

    public function testExtractAttributeAsArrayReturnsArrayOfObjects()
    {
        $attributes = ['verification' => [['status' => 'val1']]];
        $expected = new Braintree\CreditCardVerification(['status' => 'val1']);
        $this->assertEquals([$expected], Braintree\Util::extractAttributeAsArray($attributes, "verification"));
    }

    public function testDelimeterToUnderscore()
    {
        $this->assertEquals("a_b_c", Braintree\Util::delimiterToUnderscore("a-b-c"));
    }

    public function testCleanClassName()
    {
        $cn = Braintree\Util::cleanClassName('Braintree\Transaction');
        $this->assertEquals('transaction', $cn);
    }

    public function testBuildClassName()
    {
        $cn = Braintree\Util::buildClassName('creditCard');
        $this->assertEquals('Braintree\CreditCard', $cn);
    }

    public function testimplodeAssociativeArray()
    {
        $array = [
            'test1' => 'val1',
            'test2' => 'val2',
            'test3' => new DateTime('2015-05-15 17:21:00'),
        ];
        $string = Braintree\Util::implodeAssociativeArray($array);
        $this->assertEquals('test1=val1, test2=val2, test3=Fri, 15 May 2015 17:21:00 +0000', $string);
    }

    public function testVerifyKeys_withThreeLevels()
    {
        $signature = [
            'firstName',
            ['creditCard' => ['number', ['billingAddress' => ['streetAddress']]]]
        ];
        $data = [
            'firstName' => 'Dan',
            'creditCard' => [
                'number' => '5100',
                'billingAddress' => [
                    'streetAddress' => '1 E Main St'
                ]
            ]
        ];
        $this->assertNull(Braintree\Util::verifyKeys($signature, $data));
    }

    public function testVerifyKeys_withArrayOfArrays()
    {
        $signature = [
            ['addOns' => [['update' => ['amount', 'existingId']]]]
        ];

        $goodData = [
            'addOns' => [
                'update' => [
                    [
                        'amount' => '50.00',
                        'existingId' => 'increase_10',
                    ],
                    [
                        'amount' => '60.00',
                        'existingId' => 'increase_20',
                    ]
                ]
            ]
        ];

        Braintree\Util::verifyKeys($signature, $goodData);

        $badData = [
            'addOns' => [
                'update' => [
                    [
                        'invalid' => '50.00',
                    ]
                ]
            ]
        ];

        $this->expectException('InvalidArgumentException');
        Braintree\Util::verifyKeys($signature, $badData);
    }

    public function testVerifyKeys_arrayAsValue()
    {
        $signature = ['key'];
        $data = ['key' => ['value']];
        $this->expectException('InvalidArgumentException');
        Braintree\Util::verifyKeys($signature, $data);
    }

    public function testVerifyKeys()
    {
        $signature = [
                'amount', 'customerId', 'orderId', 'channel', 'paymentMethodToken', 'type',

                ['creditCard'   =>
                    ['token', 'cvv', 'expirationDate', 'number'],
                ],
                ['customer'      =>
                    [
                        'id', 'company', 'email', 'fax', 'firstName',
                        'lastName', 'phone', 'website'],
                ],
                ['billing'       =>
                    [
                        'firstName', 'lastName', 'company', 'countryName',
                        'extendedAddress', 'locality', 'postalCode', 'region',
                        'streetAddress'],
                ],
                ['shipping'      =>
                    [
                        'firstName', 'lastName', 'company', 'countryName',
                        'extendedAddress', 'locality', 'postalCode', 'region',
                        'streetAddress'],
                ],
                ['options'       =>
                    [
                        'storeInVault', 'submitForSettlement',
                        'addBillingAddressToPaymentMethod'],
                ],
                ['customFields' => ['_anyKey_']
                ],
        ];

        // test valid
        $userKeys = [
                'amount' => '100.00',
                'customFields'   => ['HEY' => 'HO',
                                          'WAY' => 'NO'],
                'creditCard' => [
                    'number' => '5105105105105100',
                    'expirationDate' => '05/12',
                    ],
                ];

        $n = Braintree\Util::verifyKeys($signature, $userKeys);
        $this->assertNull($n);

        $userKeys = [
                'amount' => '100.00',
                'customFields'   => ['HEY' => 'HO',
                                          'WAY' => 'NO'],
                'bogus' => 'FAKE',
                'totallyFake' => 'boom',
                'creditCard' => [
                    'number' => '5105105105105100',
                    'expirationDate' => '05/12',
                    ],
                ];

        // test invalid
        $this->expectException('InvalidArgumentException');

        Braintree\Util::verifyKeys($signature, $userKeys);
    }

    public function testReturnException()
    {
        $this->success = false;

        $this->expectException('Braintree\Exception\ValidationsFailed');

        Braintree\Util::returnObjectOrThrowException('Braintree\Transaction', $this);
    }

    public function testReturnObject()
    {
        $this->success = true;
        $this->transaction = new stdClass();
        $t = Braintree\Util::returnObjectOrThrowException('Braintree\Transaction', $this);
        $this->assertIsObject($t);
    }

    public function testReplaceKeyReplacesKeyWhenMatch()
    {
        $oldKey = 'googlePayCard';
        $newKey = 'androidPayCard';

        $originalParams = [
            'googlePayCard' => [
                'number' => '4111111111111111'
            ],
            'someOtherKey' => 'someOtherValue'
        ];
        $expectedParams = [
            'androidPayCard' => [
                'number' => '4111111111111111'
            ],
            'someOtherKey' => 'someOtherValue'
        ];

        $returnedParams = Braintree\Util::replaceKey($originalParams, $oldKey, $newKey);
        $this->assertEquals($returnedParams, $expectedParams);
    }

    public function testReplaceKeyDoesNotReplaceKeyWhenNoMatch()
    {
        $oldKey = 'googlePayCard';
        $newKey = 'androidPayCard';

        $originalParams = [
            'creditCard' => [
                'number' => '4111111111111111'
            ],
            'someOtherKey' => 'someOtherValue'
        ];
        $expectedParams = [
            'creditCard' => [
                'number' => '4111111111111111'
            ],
            'someOtherKey' => 'someOtherValue'
        ];

        $returnedParams = Braintree\Util::replaceKey($originalParams, $oldKey, $newKey);
        $this->assertEquals($returnedParams, $expectedParams);
    }
}
