<?php
namespace Braintree\Logging;

/**
 * Logging interface for curl requests,
 * to record the request, response and the time taken.
 *
 * @package    Braintree
 * @subpackage Logging
 */
interface CurlAuditLoggerInterface
{


    public function log();

    /**
     * Set the time the curl request was sent
     *
     * @param $requestTime
     * @return CurlAuditLoggerInterface
     */
    public function setRequestTime($requestTime);

    /**
     * Set the time the curl response was received
     *
     * @param $responseTime
     * @return CurlAuditLoggerInterface
     */
    public function setResponseTime($responseTime);

    /**
     * Set the curl and http response
     *
     * @param $arrayResponse
     * @param $httpCode
     * @return $this
     */
    public function setResponse($arrayResponse, $httpCode);

    /**
     * Set the curl and action request
     *
     * @param $arrayRequest
     * @param $action
     * @return $this
     */
    public function setRequest($arrayRequest, $action);
}
class_alias('Braintree\Logging\CurlAuditLoggerInterface', 'Braintree_Logging_CurlAuditLoggerInterface');