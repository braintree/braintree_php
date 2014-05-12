<?php
/**
 * Braintree HTTP Client
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * processes Http requests using curl
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_Http
{
    public static function delete($path)
    {
        $response = self::_doRequest('DELETE', $path);
        if($response['status'] === 200) {
            return true;
        } else {
            Braintree_Util::throwStatusCodeException($response['status']);
        }
    }

    public static function get($path)
    {
        $response = self::_doRequest('GET', $path);
        if($response['status'] === 200) {
            return Braintree_Xml::buildArrayFromXml($response['body']);
        } else {
            Braintree_Util::throwStatusCodeException($response['status']);
        }
    }

    public static function post($path, $params = null)
    {
        $response = self::_doRequest('POST', $path, self::_buildXml($params));
        $responseCode = $response['status'];
        if($responseCode === 200 || $responseCode === 201 || $responseCode === 422) {
            return Braintree_Xml::buildArrayFromXml($response['body']);
        } else {
            Braintree_Util::throwStatusCodeException($responseCode);
        }
    }

    public static function put($path, $params = null)
    {
        $response = self::_doRequest('PUT', $path, self::_buildXml($params));
        $responseCode = $response['status'];
        if($responseCode === 200 || $responseCode === 201 || $responseCode === 422) {
            return Braintree_Xml::buildArrayFromXml($response['body']);
        } else {
            Braintree_Util::throwStatusCodeException($responseCode);
        }
    }

    private static function _buildXml($params)
    {
        return empty($params) ? null : Braintree_Xml::buildXmlFromArray($params);
    }

    private static function _doRequest($httpVerb, $path, $requestBody = null)
    {
        return self::_doUrlRequest($httpVerb, Braintree_Configuration::merchantUrl() . $path, $requestBody);
    }

    public static function _doUrlRequest($httpVerb, $url, $requestBody = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpVerb);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/xml',
            'Content-Type: application/xml',
            'User-Agent: Braintree PHP Library ' . Braintree_Version::get(),
            'X-ApiVersion: ' . Braintree_Configuration::API_VERSION
        ));
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, Braintree_Configuration::publicKey() . ':' . Braintree_Configuration::privateKey());
        // curl_setopt($curl, CURLOPT_VERBOSE, true);
        if (Braintree_Configuration::sslOn()) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_CAINFO, Braintree_Configuration::caFile());
        }

        if(!empty($requestBody)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (Braintree_Configuration::sslOn()) {
            if ($httpStatus == 0) {
                throw new Braintree_Exception_SSLCertificate();
            }
        }
        return array('status' => $httpStatus, 'body' => $response);
    }
}
