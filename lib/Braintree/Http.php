<?php

namespace Braintree;

/**
 * Braintree HTTP Client
 *
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * processes Http requests using curl
 *
 * @copyright  2010 Braintree Payment Solutions
 */
class Http
{
    public static function delete($path)
    {
        $response = self::_doRequest('DELETE', $path);
        if($response['status'] === 200) {
            return true;
        } else {
            Util::throwStatusCodeException($response['status']);
        }
        return null;
    }

    public static function get($path)
    {
        $response = self::_doRequest('GET', $path);
        if($response['status'] === 200) {
            return Xml::buildArrayFromXml($response['body']);
        } else {
            Util::throwStatusCodeException($response['status']);
        }
        return null;
    }

    public static function post($path, $params = null)
    {
        $response = self::_doRequest('POST', $path, self::_buildXml($params));
        $responseCode = $response['status'];
        if($responseCode === 200 || $responseCode === 201 || $responseCode === 422) {
            return Xml::buildArrayFromXml($response['body']);
        } else {
            Util::throwStatusCodeException($responseCode);
        }
        return null;
    }

    public static function put($path, $params = null)
    {
        $response = self::_doRequest('PUT', $path, self::_buildXml($params));
        $responseCode = $response['status'];
        if($responseCode === 200 || $responseCode === 201 || $responseCode === 422) {
            return Xml::buildArrayFromXml($response['body']);
        } else {
            Util::throwStatusCodeException($responseCode);
        }
        return null;
    }

    private static function _buildXml($params)
    {
        return empty($params) ? null : Xml::buildXmlFromArray($params);
    }

    private static function _doRequest($httpVerb, $path, $requestBody = null)
    {
        return self::_doUrlRequest($httpVerb, Configuration::merchantUrl() . $path, $requestBody);
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
            'User-Agent: Braintree PHP Library ' . Version::get(),
            'X-ApiVersion: ' . Configuration::API_VERSION
        ));
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, Configuration::publicKey() . ':' . Configuration::privateKey());
        // curl_setopt($curl, CURLOPT_VERBOSE, true);
        if (Configuration::sslOn()) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_CAINFO, Configuration::caFile());
        }

        if(!empty($requestBody)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (Configuration::sslOn()) {
            if ($httpStatus == 0) {
                throw new Exception\SSLCertificate();
            }
        }
        return array('status' => $httpStatus, 'body' => $response);
    }
}
