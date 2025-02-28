<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use DateTime;
use Test\Setup;
use Braintree;

class PayPalPaymentResourceTest extends Setup
{
    private $attributes;

    public function testUpdateSignatureIsValid()
    {
        $expected = [
            'amount',
            ['amountBreakdown' =>
                [
                'discount',
                'handling',
                'insurance',
                'itemTotal',
                'shipping',
                'shippingDiscount',
                'taxTotal',
                ]
            ],
            'currencyIsoCode',
            'customField',
            'description',
            ['lineItems' =>
                [
                    'commodityCode',
                    'description',
                    'discountAmount',
                    'imageUrl',
                    'itemType',
                    'kind',
                    'name',
                    'productCode',
                    'quantity',
                    'taxAmount',
                    'totalAmount',
                    'unitAmount',
                    'unitOfMeasure',
                    'unitTaxAmount',
                    'upcCode',
                    'upcType',
                    'url'
                    ]
            ],
            'orderId',
            'payeeEmail',
            'paymentMethodNonce',
            ['shipping' =>
                [
                'company',
                'countryCodeAlpha2',
                'countryCodeAlpha3',
                'countryCodeNumeric',
                'countryName',
                'extendedAddress',
                'firstName',
                ['internationalPhone' =>
                 [
                    'countryCode',
                    'nationalNumber',
                 ]
                 ],
                'lastName',
                'locality',
                'postalCode',
                'region',
                'streetAddress',
              ]
            ],
            ['shippingOptions' =>
             [
                'amount',
                'id',
                'label',
                'selected',
                'type',
                ]
            ]
             ];

        $this->assertEquals($expected, Braintree\PayPalPaymentResourceGateway::updateSignature());
    }
}
