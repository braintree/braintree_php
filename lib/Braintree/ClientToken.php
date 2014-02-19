<?php

class Braintree_ClientToken
{
    public static function generate($params=array())
    {
        $generateParams = null;
        if ($params) {
            self::conditionallyVerifyKeys($params);
            $generateParams = array("client_token" => $params);
        }

        return self::_doGenerate('/client_token', $generateParams);
    }

    /**
     * sends the generate request to the gateway
     *
     * @ignore
     * @param var $url
     * @param array $params
     * @return mixed
     */
    public static function _doGenerate($url, $params)
    {
        $response = Braintree_Http::post($url, $params);

        return self::_verifyGatewayResponse($response);
    }

    public static function conditionallyVerifyKeys($params)
    {
        if (array_key_exists("customerId", $params)) {
            Braintree_Util::verifyKeys(self::generateWithCustomerIdSignature(), $params);
        } else {
            Braintree_Util::verifyKeys(self::generateWithoutCustomerIdSignature(), $params);
        }
    }

    public static function generateWithCustomerIdSignature()
    {
        return array("customerId", "proxyMerchantId", array("options" => array("makeDefault", "verifyCard", "failOnDuplicatePaymentMethod")));
    }

    public static function generateWithoutCustomerIdSignature()
    {
        return array("proxyMerchantId");
    }

    /**
     * generic method for validating incoming gateway responses
     *
     * If the request is successful, returns a client token string.
     * Otherwise, throws an InvalidArgumentException with the error
     * response from the Gateway or an HTTP status code exception.
     *
     * @ignore
     * @param array $response gateway response values
     * @return string client token
     * @throws InvalidArgumentException | HTTP status code exception
     */
    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['clientToken'])) {
            return $response['clientToken']['value'];
        } elseif (isset($response['apiErrorResponse'])) {
            throw new InvalidArgumentException(
                $response['apiErrorResponse']['message']
            );
        } else {
            throw new Braintree_Exception_Unexpected(
                "Expected clientToken or apiErrorResponse"
            );
        }
    }

}
