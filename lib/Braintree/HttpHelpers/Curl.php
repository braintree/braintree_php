<?php
namespace Braintree\HttpHelpers;

use Braintree\Version;
use Braintree\Configuration;
use finfo;

class Curl {

    public static function makeRequest($httpVerb, $url, $config, $httpRequest, $requestBody = null, $file = null, $customHeaders = null, $useClientCredentials = false)
    {
        $httpRequest->setOption(CURLOPT_TIMEOUT, $config->getTimeout());
        $httpRequest->setOption(CURLOPT_CUSTOMREQUEST, $httpVerb);
        $httpRequest->setOption(CURLOPT_URL, $url);

        if ($config->getAcceptGzipEncoding()) {
            $httpRequest->setOption(CURLOPT_ENCODING, 'gzip');
        }

        if ($config->getSslVersion()) {
            $httpRequest->setOption(CURLOPT_SSLVERSION, $config->getSslVersion());
        }

        $headers = [];
        if ($customHeaders) {
            $headers = $customHeaders;
        } else {
            $headers[] = 'Accept: application/xml';
            $headers[] = 'User-Agent: Braintree PHP Library ' . Version::get();
            $headers[] = 'X-ApiVersion: ' . Configuration::API_VERSION;
            $headers[] = 'Content-Type: application/xml';
        }

        $authorization = self::_getAuthorization($config, $useClientCredentials);
        if (isset($authorization['user'])) {
            $httpRequest->setOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $httpRequest->setOption(CURLOPT_USERPWD, $authorization['user'] . ':' . $authorization['password']);
        } else if (isset($authorization['token'])) {
            $headers[] = 'Authorization: Bearer ' . $authorization['token'];
        }

        if ($config->sslOn()) {
            $httpRequest->setOption(CURLOPT_SSL_VERIFYPEER, true);
            $httpRequest->setOption(CURLOPT_SSL_VERIFYHOST, 2);
            $httpRequest->setOption(CURLOPT_CAINFO, self::_getcafile($config));
        }

        if (!empty($file)) {
            $boundary = "---------------------" . md5(mt_rand() . microtime());
            $headers[] = "Content-Type: multipart/form-data; boundary={$boundary}";
            self::_prepareMultipart($httpRequest, $requestBody, $file, $boundary);
        }

        $httpRequest->setOption(CURLOPT_HTTPHEADER, $headers);
    }

    private static function _getAuthorization($config, $useClientCredentials)
    {
        if ($useClientCredentials) {
            return [
                'user' => $config->getClientId(),
                'password' => $config->getClientSecret(),
            ];
        } else if ($config->isAccessToken()) {
            return [
                'token' => $config->getAccessToken(),
            ];
        } else {
            return [
                'user' => $config->getPublicKey(),
                'password' => $config->getPrivateKey(),
            ];
        }
    }

    private static function _getCaFile($config)
    {
        static $memo;

        if ($memo === null) {
            $caFile = $config->caFile();

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

    private static function _prepareMultipart($httpRequest, $requestBody, $file, $boundary)
    {
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
        $httpRequest->setOption(CURLOPT_POST, true);
        $httpRequest->setOption(CURLOPT_POSTFIELDS, implode("\r\n", $body));
    }
}
