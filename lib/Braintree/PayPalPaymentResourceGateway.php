<?php // phpcs:disable PEAR.Commenting

namespace Braintree;

class PayPalPaymentResourceGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Http($gateway->config);
    }

    public function update($attribs)
    {
        Util::verifyKeys(self::updateSignature(), $attribs);
        $path = $this->_config->merchantPath() . '/paypal/payment_resource';
        $response = $this->_http->put($path, ['paypal_payment_resource' => $attribs]);
        return $this->_verifyGatewayResponse($response);
    }

    public static function updateSignature()
    {
        return [
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
                ],
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
                ],
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
                        ],
                    ],
                    'lastName',
                    'locality',
                    'postalCode',
                    'region',
                    'streetAddress',
                ],
            ],
            ['shippingOptions' =>
                [
                    'amount',
                    'id',
                    'label',
                    'selected',
                    'type',
                ],
            ],
        ];
    }

    private function _verifyGatewayResponse($response)
    {
        if (isset($response['paymentMethodNonce'])) {
            return new Result\Successful(
                PaymentMethodNonce::factory($response['paymentMethodNonce']),
                "paymentMethodNonce"
            );
        } elseif (isset($response['apiErrorResponse'])) {
            return new Result\Error($response['apiErrorResponse']);
        } else {
            throw new Exception\Unexpected(
                "Expected address or apiErrorResponse"
            );
        }
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __toString()
    {
        return __CLASS__ . '[' .
                Util::attributesToString($this->_attributes) . ']';
    }
}
