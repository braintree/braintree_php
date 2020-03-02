<?php
namespace Braintree;

use finfo;

/**
 * Braintree HTTP Client
 * processes Http requests using curl
 */
class Http
{
    protected $_config;
    private $_useClientCredentials = false;

    public function __construct($config)
    {
        $this->_config = $config;
    }

    public function delete($path, $params = null)
    {
        $response = $this->_doRequest('DELETE', $path, $this->_buildXml($params));
        $responseCode = $response['status'];
        if ($responseCode === 200 || $responseCode === 204) {
            return true;
        } else if ($responseCode === 422) {
            return Xml::buildArrayFromXml($response['body']);
        } else {
            Util::throwStatusCodeException($response['status']);
        }
    }

    public function get($path)
    {
        $response = $this->_doRequest('GET', $path);
        if ($response['status'] === 200) {
            return Xml::buildArrayFromXml($response['body']);
        } else {
            Util::throwStatusCodeException($response['status']);
        }
    }

    public function post($path, $params = null)
    {
        $response = $this->_doRequest('POST', $path, $this->_buildXml($params));
        $responseCode = $response['status'];
        if ($responseCode === 200 || $responseCode === 201 || $responseCode === 422 || $responseCode == 400) {
            return Xml::buildArrayFromXml($response['body']);
        } else {
            Util::throwStatusCodeException($responseCode);
        }
    }

    public function postMultipart($path, $params, $file)
    {
        $headers = [
            'User-Agent: Braintree PHP Library ' . Version::get(),
            'X-ApiVersion: ' . Configuration::API_VERSION
        ];
        $response = $this->_doRequest('POST', $path, $params, $file, $headers);
        $responseCode = $response['status'];
        if ($responseCode === 200 || $responseCode === 201 || $responseCode === 422 || $responseCode == 400) {
            return Xml::buildArrayFromXml($response['body']);
        } else {
            Util::throwStatusCodeException($responseCode);
        }
    }

    public function put($path, $params = null)
    {
        $response = $this->_doRequest('PUT', $path, $this->_buildXml($params));
        $responseCode = $response['status'];
        if ($responseCode === 200 || $responseCode === 201 || $responseCode === 422 || $responseCode == 400) {
            return Xml::buildArrayFromXml($response['body']);
        } else {
            Util::throwStatusCodeException($responseCode);
        }
    }

    private function _buildXml($params)
    {
        return empty($params) ? null : Xml::buildXmlFromArray($params);
    }

    private function _getAuthorization()
    {
        if ($this->_useClientCredentials) {
            return [
                'user' => $this->_config->getClientId(),
                'password' => $this->_config->getClientSecret(),
            ];
        } else if ($this->_config->isAccessToken()) {
            return [
                'token' => $this->_config->getAccessToken(),
            ];
        } else {
            return [
                'user' => $this->_config->getPublicKey(),
                'password' => $this->_config->getPrivateKey(),
            ];
        }
    }

    public function useClientCredentials()
    {
        $this->_useClientCredentials = true;
    }

    private function _doRequest($httpVerb, $path, $requestBody = null, $file = null, $headers = null)
    {
        return $this->_doUrlRequest($httpVerb, $this->_config->baseUrl() . $path, $requestBody, $file, $headers);
    }

    /**
     * This function gives integration test ability to mock request-response.
     *
     * @param $httpVerb
     * @param $url
     * @param array $headers
     * @param null $requestBody
     * @param null $file
     * @return array
     * @throws Exception\SSLCaFileNotFound
     */
    public function doCurlRequest($httpVerb, $url, array $headers, $requestBody = null, $file = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->_config->timeout());
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpVerb);
        curl_setopt($curl, CURLOPT_URL, $url);

        if ($this->_config->acceptGzipEncoding()) {
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        }
        if ($this->_config->sslVersion()) {
            curl_setopt($curl, CURLOPT_SSLVERSION, $this->_config->sslVersion());
        }

        $authorization = $this->_getAuthorization();
        if (isset($authorization['user'])) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $authorization['user'] . ':' . $authorization['password']);
        }

        if ($this->_config->sslOn()) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_CAINFO, $this->getCaFile());
        }

        if (!empty($file)) {
            $boundary = "---------------------" . md5(mt_rand() . microtime());
            $headers[] = "Content-Type: multipart/form-data; boundary={$boundary}";
            $this->prepareMultipart($curl, $requestBody, $file, $boundary);
        } else if (!empty($requestBody)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
        }

        if ($this->_config->isUsingProxy()) {
            $proxyHost = $this->_config->getProxyHost();
            $proxyPort = $this->_config->getProxyPort();
            $proxyType = $this->_config->getProxyType();
            $proxyUser = $this->_config->getProxyUser();
            $proxyPwd= $this->_config->getProxyPassword();
            curl_setopt($curl, CURLOPT_PROXY, $proxyHost . ':' . $proxyPort);
            if (!empty($proxyType)) {
                curl_setopt($curl, CURLOPT_PROXYTYPE, $proxyType);
            }
            if ($this->_config->isAuthenticatedProxy()) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxyUser . ':' . $proxyPwd);
            }
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        $response = curl_exec($curl);

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $responseHeader = array_filter(explode("\r\n", substr($response, 0, $headerSize)));
        $response = substr($response, $headerSize);

        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error_code = curl_errno($curl);
        $error = curl_error($curl);

        curl_close($curl);

        return [
            'status' => $httpStatus,
            'header' => $responseHeader,
            'body' => $response,
            'error_code' => $error_code,
            'error_message' => $error,
        ];
    }


    /**
     * Inherit this function if you want to log request-response data
     *
     * @param array $request
     * @param array $response
     */
    protected function onRequestDone(array $request, array $response)
    {
        // ..
    }

    public function _doUrlRequest($httpVerb, $url, $requestBody = null, $file = null, $customHeaders = null)
    {
        if ($customHeaders) {
            $headers = $customHeaders;
        } else {
            $headers = [
                'Accept: application/xml',
                'User-Agent: Braintree PHP Library ' . Version::get(),
                'X-ApiVersion: ' . Configuration::API_VERSION,
                'Content-Type: application/xml',
            ];
        }

        $authorization = $this->_getAuthorization();
        if (isset($authorization['token'])) {
            $headers[] = 'Authorization: Bearer ' . $authorization['token'];
        }

        $response = $this->doCurlRequest($httpVerb, $url, $headers, $requestBody, $file);

        $this->onRequestDone([
                'method' => $httpVerb,
                'url' => $url,
                'header' => $headers,
                'body' => $requestBody,
                'file' => $file,
            ],
            $response
        );

        if ($response['error_code'] == 28 && $response['status'] == 0) {
            throw new Exception\Timeout();
        }

        if ($this->_config->sslOn()) {
            if ($response['status'] == 0) {
                throw new Exception\SSLCertificate($response['error_message'], $response['error_code']);
            }
        } else if ($response['error_code']) {
            throw new Exception\Connection($response['error_message'], $response['error_code']);
        }

        return $response;
    }

    function prepareMultipart($ch, $requestBody, $file, $boundary) {
        $disallow = ["\0", "\"", "\r", "\n"];
        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $filePath = stream_get_meta_data($file)['uri'];
        $data = file_get_contents($filePath);
        $mimeType = $fileInfo->buffer($data);

        // build normal parameters
        foreach ($requestBody as $k => $v) {
            $k = str_replace($disallow, "_", $k);
            $body[] = implode("\r\n", [
                "Content-Disposition: form-data; name=\"{$k}\"",
                "",
                filter_var($v),
            ]);
        }

        // build file parameter
        $splitFilePath = explode(DIRECTORY_SEPARATOR, $filePath);
        $filePath = end($splitFilePath);
        $filePath = str_replace($disallow, "_", $filePath);
        $body[] = implode("\r\n", [
            "Content-Disposition: form-data; name=\"file\"; filename=\"{$filePath}\"",
            "Content-Type: {$mimeType}",
            "",
            $data,
        ]);

        // add boundary for each parameters
        array_walk($body, function (&$part) use ($boundary) {
            $part = "--{$boundary}\r\n{$part}";
        });

        // add final boundary
        $body[] = "--{$boundary}--";
        $body[] = "";

        // set options
        return curl_setopt_array($ch, [
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => implode("\r\n", $body)
        ]);
    }

    private function getCaFile()
    {
        static $memo;

        if ($memo === null) {
            $caFile = $this->_config->caFile();

            if (substr($caFile, 0, 7) !== 'phar://') {
                return $caFile;
            }

            $extractedCaFile = sys_get_temp_dir() . '/api_braintreegateway_com.ca.crt';

            if (!file_exists($extractedCaFile) || sha1_file($extractedCaFile) != sha1_file($caFile)) {
                if (!copy($caFile, $extractedCaFile)) {
                    throw new Exception\SSLCaFileNotFound();
                }
            }
            $memo = $extractedCaFile;
        }

        return $memo;
    }
}
class_alias('Braintree\Http', 'Braintree_Http');
