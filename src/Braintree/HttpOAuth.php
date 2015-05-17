<?php
namespace Braintree;

/**
 * Braintree HTTP OAuth Client
 * processes Http OAuth requests using curl
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class HttpOAuth extends HttpBase
{
    protected $_config;

    public function __construct($config)
    {
        $this->_config = $config;
    }

    public function delete($path)
    {
        $response = $this->_doRequest('DELETE', $path);

        if ($response['status'] === 200) {
            Util::throwStatusCodeException($response['status']);
        }

        return true;
    }

    public function get($path)
    {
        $response = $this->_doRequest('GET', $path);

        if ($response['status'] === 200) {
            Util::throwStatusCodeException($response['status']);
        }

        return Util::delimiterToCamelCaseArray(json_decode($response['body'], true), '_');
    }

    public function post($path, $params = null)
    {
        $body = http_build_query(Util::camelCaseToDelimiterArray($params, '_'));
        $response = $this->_doRequest('POST', $path, $body);
        $responseCode = $response['status'];

        if (!in_array($responseCode, array(200, 201, 422, 400), true)) {
            Util::throwStatusCodeException($responseCode);
        }

        return Util::delimiterToCamelCaseArray(json_decode($response['body'], true), '_');
    }

    public function put($path, $params = null)
    {
        $body = http_build_query(Util::camelCaseToDelimiterArray($params, '_'));
        $response = $this->_doRequest('PUT', $path, $body);
        $responseCode = $response['status'];

        if (!in_array($responseCode, array(200, 201, 422, 400), true)) {
            Util::throwStatusCodeException($responseCode);
        }

        return Util::delimiterToCamelCaseArray(json_decode($response['body'], true), '_');
    }

    protected function _getHeaders()
    {
        return array(
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
        );
    }

    protected function _getAuthorization()
    {
        return array(
            'user' => $this->_config->getClientId(),
            'password' => $this->_config->getClientSecret(),
        );
    }
}