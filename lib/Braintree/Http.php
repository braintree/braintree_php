<?php
/**
 * Braintree HTTP Client based on Zend_Http_Client
 *
 * @see        Zend_Http_Client
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * processes Http requests using a Zend_Http_Client instance
 *
 * @see        Zend_Http_Client
 * @copyright  2010 Braintree Payment Solutions
 */
class Braintree_Http
{
    /**
     * delete request
     * @access public
     * @param var $path url path to access
     * @return boolean success or failure
     */
    public static function delete($path)
    {
        $response = self::_doRequest('DELETE', $path);
        if($response->getStatus() === 200) {
            return true;
        } else {
            Braintree_Util::throwStatusCodeException($response->getStatus());
        }
    }
    /**
     * request data via get
     * @access public
     * @param var $path url path to access
     * @return array array of XML data
     */
    public static function get($path)
    {
        $response = self::_doRequest('GET', $path);
        if($response->getStatus() === 200) {
            return Braintree_Xml::buildArrayFromXml($response->getBody());
        } else {
            Braintree_Util::throwStatusCodeException($response->getStatus());
        }
    }
    /**
     * post xml data to the gateway
     * @param var $path
     * @param array $params
     * @return array
     */
    public static function post($path, $params = null)
    {
        $response = self::_doRequest('POST', $path, self::_buildXml($params));
        $responseCode = $response->getStatus();
        if($responseCode === 200 || $responseCode === 201 || $responseCode === 422) {
            return Braintree_Xml::buildArrayFromXml($response->getBody());
        } else {
            Braintree_Util::throwStatusCodeException($responseCode);
        }
    }

    /**
     * put xml data to the gateway
     * @param var $path
     * @param array $params
     * @return array
     */
    public static function put($path, $params = null)
    {
        $response = self::_doRequest('PUT', $path, self::_buildXml($params));
        $responseCode = $response->getStatus();
        if($responseCode === 200 || $responseCode === 201 || $responseCode === 422) {
            return Braintree_Xml::buildArrayFromXml($response->getBody());
        } else {
            Braintree_Util::throwStatusCodeException($responseCode);
        }
    }
    /**
     * build outgoing XML
     * @param array $params
     * @return mixed array or null
     */
    private static function _buildXml($params)
    {
        return empty($params) ? null : Braintree_Xml::buildXmlFromArray($params);
    }
    /**
     * gzip encoding is automatic depending on the status of the zip extension
     * 
     * @param var $httpVerb
     * @param var $path
     * @param var $requestBody
     * @return object Zend_Http_Client_Response
     */
    private static function _doRequest($httpVerb, $path, $requestBody = null)
    {
        // set access to configuration
        //$config = Braintree_Configuration::singleton();
        // create an http client
        $connection = new Zend_Http_Client();
        // if ssl is on, special options need to be sent
        // to the http client to send the ssl params
        if(Braintree_Configuration::sslOn()) {
            $streamOpts = array(
                'ssl' => array(
                    'verify_peer' => true,
                    'cafile'        => Braintree_Configuration::caFile(),
                    )
                );

            // create a socket adapter
            $adapter = new Zend_Http_Client_Adapter_Socket();
            // attach the adapter to the client
            $connection->setAdapter($adapter);
            $adapter->setStreamContext($streamOpts);
        }

        $connection->setUri(Braintree_Configuration::merchantUrl().$path);
        // http method
        $connection->setMethod($httpVerb);
        // headers
        $connection->setHeaders(array(
            'Accept' => 'application/xml',
            'User-Agent' => 'Braintree PHP Library ' . Braintree_Version::get(),
            'X-ApiVersion' => Braintree_Configuration::API_VERSION,
            ));

        // authentication
        $connection->setAuth(Braintree_Configuration::publicKey(),
                             Braintree_Configuration::privateKey(),
                             Zend_Http_Client::AUTH_BASIC
                            );

        // body
        if(!empty($requestBody)) {
            if ($httpVerb == 'PUT') {
                $connection->setHeaders(array('X-Http-Method-Override' => 'PUT'));
                $connection->setMethod('POST');
            }
            $connection->setRawData($requestBody, 'application/xml');
           //Configuration.logger.debug _format_and_sanitize_body_for_log(body)
        }

        // fire the request
        $response = $connection->request();

        return $response;

    }
}
