<?php
namespace Braintree\Logging;

/**
 * Raised when authentication fails.
 * This may be caused by an incorrect Configuration
 *
 * @package    Braintree
 * @subpackage Logging
 */
interface CurlAuditLoggerInterface
{


    public function log();

    /**
     * @param $responseTime
     * @return CurlAuditLoggerInterface
     */
    public function setResponseTime($responseTime);

    /**
     * @param $requestTime
     * @return CurlAuditLoggerInterface
     */
    public function setRequestTime($requestTime);

    /**
     * @param $arrayResponse
     * @param $httpCode
     * @return $this
     */
    public function setResponse($arrayResponse, $httpCode);

    /**
     * @param $arrayRequest
     * @param $action
     * @return $this
     */
    public function setRequest($arrayRequest, $action);
}
class_alias('Braintree\Logging\CurlAuditLoggerInterface', 'Braintree_Logging_CurlAuditLoggerInterface');