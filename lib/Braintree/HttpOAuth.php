<?php
/**
 * Braintree HTTP OAuth Client
 * processes Http OAuth requests using curl
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_HttpOAuth
{
    protected $_config;

    public function __construct($config)
    {
        $this->_config = $config;
    }

    public function delete($path)
    {
        $response = $this->_doRequest('DELETE', $path);
        if($response['status'] === 200) {
            return true;
        } else {
            Braintree_Util::throwStatusCodeException($response['status']);
        }
    }

    public function get($path)
    {
        $response = $this->_doRequest('GET', $path);
        if($response['status'] === 200) {
            return Braintree_Util::delimiterToCamelCaseArray(json_decode($response['body'], true), '_');
        } else {
            Braintree_Util::throwStatusCodeException($response['status']);
        }
    }

    public function post($path, $params = null)
    {
        $body = http_build_query(Braintree_Util::camelCaseToDelimiterArray($params, '_'));
        $response = $this->_doRequest('POST', $path, $body);
        $responseCode = $response['status'];
        if($responseCode === 200 || $responseCode === 201 || $responseCode === 422 || $responseCode === 400) {
            return Braintree_Util::delimiterToCamelCaseArray(json_decode($response['body'], true), '_');
        } else {
            Braintree_Util::throwStatusCodeException($responseCode);
        }
    }

    public function put($path, $params = null)
    {
        $body = http_build_query(Braintree_Util::camelCaseToDelimiterArray($params, '_'));
        $response = $this->_doRequest('PUT', $path, $body);
        $responseCode = $response['status'];
        if($responseCode === 200 || $responseCode === 201 || $responseCode === 422 || $responseCode === 400) {
            return Braintree_Util::delimiterToCamelCaseArray(json_decode($response['body'], true), '_');
        } else {
            Braintree_Util::throwStatusCodeException($responseCode);
        }
    }

    private function _doRequest($httpVerb, $path, $requestBody = null)
    {
        return $this->_doUrlRequest($httpVerb, $this->_config->baseUrl() . $path, $requestBody);
    }

    public function _doUrlRequest($httpVerb, $url, $requestBody = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpVerb);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Braintree PHP Library ' . Braintree_Version::get(),
            'X-ApiVersion: ' . Braintree_Configuration::API_VERSION
        );
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $this->_config->getClientId() . ':' . $this->_config->getClientSecret());
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        // curl_setopt($curl, CURLOPT_VERBOSE, true);
        if ($this->_config->sslOn()) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_CAINFO, $this->_config->caFile());
        }

        if(!empty($requestBody)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($this->_config->sslOn()) {
            if ($httpStatus == 0) {
                throw new Braintree_Exception_SSLCertificate();
            }
        }
        return array('status' => $httpStatus, 'body' => $response);
    }
}
