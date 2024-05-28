<?php

namespace Braintree\HttpHelpers;

use Braintree\Configuration;

interface HttpClient
{
    /**
     * DELETE request
     *
     * @param string $path URL path
     * @param array|null $params optional any addition request parameters
     *
     * @return array
     * @throws \Exception
     */
    public function delete($path, $params = null);

    /**
    * GET request
    *
    * @param string $path URL path
    *
    * @return array
     *@throws \Exception
    */
    public function get($path);

    /**
     * POST request for multi parts to be sent
     *
     * @param string $path URL path
     * @param array|null $params additional request parameters
     * @param resource $file to be uploaded
     *
     * @return array
     * @throws \Exception
     */
    public function postMultipart($path, $params, $file);

    /**
     * PUT request
     *
     * @param string $path URL path
     * @param array|null $params optional any addition request parameters
     *
     * @return array
     * @throws \Exception
     */
    public function put($path, $params = null);

    /**
     * @return void
     */
    public function useClientCredentials();

    public function setConfig(Configuration $config): self;
}