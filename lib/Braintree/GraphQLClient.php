<?php

namespace Braintree;

/**
 * Braintree GraphQL Client
 * process GraphQL requests using curl
 */
class GraphQLClient
{
    protected $_service = null;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($config)
    {
        $this->_service = new GraphQL($config);
    }

    /*
     * Make a GraphQL API request
     *
     * @param object $definition of the query
     * @param object $variables optional
     *
     * @return object result
     */
    public function query($definition, $variables = null)
    {
        return $this->_service->request($definition, $variables);
    }

    /**
     * Extract validation errors from the GraphQL response
     *
     * @param array $response The GraphQL response
     *
     *  @return array|null validation errors or null
     */
    public static function getValidationErrors($response)
    {
        if (!isset($response['errors']) || !is_array($response['errors'])) {
            return null;
        }
        $validationErrors = array_map(function ($error) {
            return ['attribute' => '', 'code' => GraphQLClient::getValidationErrorCode($error), 'message' => $error["message"]];
        }, $response['errors']);
        return ['errors' => $validationErrors];
    }

    private static function getValidationErrorCode($error)
    {
        if (!isset($error['extensions'])) {
            return null;
        }
        $extensions = $error['extensions'];

        if (!isset($extensions['legacyCode'])) {
            return null;
        }

        return $extensions['legacyCode'];
    }
}
