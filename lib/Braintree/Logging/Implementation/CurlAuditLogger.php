<?php

namespace Braintree\Logging;

use DateTime;

/**
 * Braintree Curl Audit Logger
 * Logger for Request, Response and Duration information of Curl requests
 *
 * @package   Braintree
 */

class CurlAuditLogger implements CurlAuditLoggerInterface{

    /**
     * @var CurlAuditLoggerInterface
     */
    protected $logger;

    /**
     * The Request
     *
     * @var
     */
    protected $request;

    /**
     * The Response
     *
     * @var
     */
    protected $response;

    /**
     * Time of Request
     *
     * @var DateTime
     */
    protected $requestTime;

    /**
     * Time of Response
     *
     * @var DateTime
     */
    protected $responseTime;


    /**
     * @var $loggingRef string
     */
    protected $loggingRef;

    /**
     * @var int
     */
    protected $start;

    /**
     * @var int
     */
    protected $end;

    /**
     * @var $httpResponse int
     */
    protected $httpResponse;

    /**
     * @var $action
     */
    protected $action;

    /**
     * @param string $loggingRef
     * @return $this
     */
    public function setLoggingRef($loggingRef)
    {
        $this->loggingRef = $loggingRef;
        return $this;
    }

    /**
     * @return string
     */
    public function getLoggingRef()
    {
        return $this->loggingRef;
    }

    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    public function getAction()
    {
        return $this->action;
    }

    /**
     * Actual logging function
     */
    public function log()
    {
        $priority = Logger::INFO;
        $request = $this->getRequest();
        $response = $this->getResponse();
        $errorCode = null;

        if ($response && isset($response['REQUEST']['RESPONSE']['ERROR'])) {
            $errorCode = $response['REQUEST']['RESPONSE']['ERROR']['CODE'];
        }

        $this->getLogger()->log(
            $priority,
            $request['REQUEST']['ACTION'],
            array(
                'request_id' => ($response) ? $response['REQUEST']['RESPONSE']['META']['REQUESTID'] : null,
                'merchant_id' => $request['REQUEST']['META']['MERCHANTID'],
                'result' => ($response) ? $response['REQUEST']['RESPONSE']['RESULT'] : null,
                'error_code' => $errorCode,
                'request_time' => $this->getRequestTime()->format('Y-m-d H:i:s'),
                'request_data' => json_encode($request),
                'request_duration' => $this->getDuration(),
                'response_data' => ($this->getResponse()) ? json_encode($response) : null,
                'logging_ref'   => $this->getLoggingRef(),
                'http_response' => $this->getHttpResponse(),
                'action' => $this->getAction(),
            )
        );
    }

    /**
     * @return number
     */
    public function getDuration()
    {
        $end = $this->end;
        if ($end == false) {
            $end = microtime(true);
        }
        return abs(round(($end - $this->start) * 1000));
    }

    /**
     * @param $arrayRequest
     * @return $this
     */
    public function setRequest($arrayRequest, $action)
    {
        $this->request = $arrayRequest;
        $this->response = '';
        $this->action = $action;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param $requestTime
     * @return $this
     */
    public function setRequestTime($requestTime)
    {
        $this->start = $requestTime;

        $date = new DateTime();
        $date->setTimestamp($requestTime);
        $this->requestTime = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRequestTime()
    {
        return $this->requestTime;
    }

    /**
     * @param $arrayResponse
     * @return $this
     */
    public function setResponse($arrayResponse, $httpResponse)
    {
        $this->response = $arrayResponse;
        $this->httpResponse = $httpResponse;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param $responseTime
     * @return $this
     */
    public function setResponseTime($responseTime)
    {
        $this->end = $responseTime;

        $date = new DateTime();
        $date->setTimestamp($responseTime);
        $this->responseTime = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getResponseTime()
    {
        return $this->responseTime;
    }
}