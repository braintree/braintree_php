<?php

namespace Braintree;

/**
 * Provides methods to interact with Bank Account Instant Verification functionality.
 *
 * This gateway enables merchants to create JWTs for initiating the Open Banking flow
 * and retrieve bank account details for display purposes.
 */
class BankAccountInstantVerificationGateway
{
    private $_gateway;
    private $_http;
    private $_config;
    private $_graphQLClient;

    private static $CREATE_JWT_MUTATION =
        'mutation CreateBankAccountInstantVerificationJwt($input: CreateBankAccountInstantVerificationJwtInput!) { ' .
        'createBankAccountInstantVerificationJwt(input: $input) {' .
        '    jwt' .
        '  }' .
        '}';

    /**
     * BankAccountInstantVerificationGateway constructor.
     *
     * @param object $gateway The gateway object providing configuration and GraphQL client.
     */
    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_http = new Http($gateway->config);
        $this->_config = $gateway->config;
        $this->_graphQLClient = $gateway->graphQLClient;
    }

    /**
     * Creates a Bank Account Instant Verification JWT for initiating the Open Banking flow.
     *
     * @param BankAccountInstantVerificationJwtRequest $request the JWT creation request containing business name and redirect URLs
     *
     * @return Result\Successful|Result\Error a Result containing the JWT and client mutation ID
     */
    public function createJwt($request)
    {
        $response = $this->_graphQLClient->query(self::$CREATE_JWT_MUTATION, $request->toGraphQLVariables());
        $errors = GraphQLClient::getValidationErrors($response);

        if ($errors) {
            return new Result\Error(['errors' => $errors]);
        }

        try {
            $data = $response['data'];
            $result = $data['createBankAccountInstantVerificationJwt'];

            $jwt = $result['jwt'];

            $jwtObject = BankAccountInstantVerificationJwt::factory([
                'jwt' => $jwt
            ]);

            return new Result\Successful($jwtObject, 'bankAccountInstantVerificationJwt');
        } catch (\Exception $e) {
            throw new Exception\Unexpected("Couldn't parse response: " . $e->getMessage());
        }
    }
}
