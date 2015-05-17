<?php
namespace Braintree;

/**
 * Braintree HTTP Client
 * processes Http requests using curl
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Http extends HttpBase
{
    protected $_config;

    public function __construct($config)
    {
        $this->_config = $config;
    }

    public function delete($path)
    {
        $response = $this->_doRequest('DELETE', $path);

        if ($response['status'] !== 200) {
            Util::throwStatusCodeException($response['status']);
        }

        return true;
    }

    public function get($path)
    {
        $response = $this->_doRequest('GET', $path);

        if ($response['status'] !== 200) {
            Util::throwStatusCodeException($response['status']);
        }

        return Xml::buildArrayFromXml($response['body']);
    }

    public function post($path, $params = null)
    {
        $response = $this->_doRequest('POST', $path, $this->_buildXml($params));
        $responseCode = $response['status'];

        if (!in_array($responseCode, array(200, 201, 422), true)) {
            Util::throwStatusCodeException($responseCode);
        }

        return Xml::buildArrayFromXml($response['body']);
    }

    public function put($path, $params = null)
    {
        $response = $this->_doRequest('PUT', $path, $this->_buildXml($params));
        $responseCode = $response['status'];

        if (!in_array($responseCode, array(200, 201, 422), true)) {
            Util::throwStatusCodeException($responseCode);
        }

        return Xml::buildArrayFromXml($response['body']);
    }

    private function _buildXml($params)
    {
        return empty($params) ? null : Xml::buildXmlFromArray($params);
    }

    protected function _getHeaders()
    {
        return array(
            'Accept: application/xml',
            'Content-Type: application/xml',
        );
    }

    protected function _getAuthorization()
    {
        if ($this->_config->isAccessToken()) {
            return array(
                'token' => $this->_config->getAccessToken(),
            );
        } else {
            return array(
                'user' => $this->_config->getClientId(),
                'password' => $this->_config->getClientSecret(),
            );
        }
    }
}