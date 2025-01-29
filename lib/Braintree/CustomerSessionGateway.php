<?php

namespace Braintree;

use Braintree\GraphQL\Unions\CustomerRecommendations;
use Braintree\GraphQL\Types\CustomerRecommendationsPayload;
use Braintree\GraphQL\Types\PaymentOptions;
use Braintree\GraphQL\Inputs\CreateCustomerSessionInput;
use Braintree\GraphQL\Inputs\UpdateCustomerSessionInput;
use Braintree\GraphQL\Inputs\CustomerRecommendationsInput;

/**
 * Creates and manages PayPal customer sessions.
 */
class CustomerSessionGateway
{
    private $graphQLClient;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($graphQLClient)
    {
        $this->graphQLClient = $graphQLClient;
    }

    /**
     * Creates a new customer session.
     *
     * Example:
     *   $customer = CustomerSessionInput::builder()
     *      ->email($email)
     *      ->build();
     *
     *   $input = CreateCustomerSessionInput::builder()
     *      ->customer($customer)
     *      ->build();
     *
     *  $result = $gateway->customerSession()->createCustomerSession($input);
     *
     *  if ($result->success) {
     *    print_r("success!: " . $result->sessionId);
     *  }
     *
     * @param CreateCustomerSessionInput $input The input parameters for creating a customer session.
     *
     * @return Result\Error|Result\Successful A result object with session ID if successful, or errors otherwise.
     *
     * @throws Exception\Unexpected If there is an unexpected error during the process.
     */
    public function createCustomerSession(CreateCustomerSessionInput $input)
    {
        return $this->executeMutation(self::CREATE_CUSTOMER_SESSION_MUTATION, $input, 'createCustomerSession');
    }

    /**
     * Updates an existing customer session.
     *
     * Example:
     *   $customer = CustomerSessionInput::builder()
     *      ->email($email)
     *      ->build();
     *
     *   $input = UpdateCustomerSessionInput::builder($sessionId)
     *      ->customer($customer)
     *      ->build();
     *
     *  $result = $gateway->customerSession()->updateCustomerSession($input);
     *
     *  if ($result->success) {
     *    print_r("success!: " . $result->sessionId);
     *  }
     *
     * @param UpdateCustomerSessionInput $input The input parameters for updating a customer session.
     *
     * @return Result\Error|Result\Successful A result object with session ID if successful, or errors otherwise.
     *
     * @throws Exception\Unexpected If there is an unexpected error during the process.
     */
    public function updateCustomerSession($input)
    {
        return $this->executeMutation(self::UPDATE_CUSTOMER_SESSION_MUTATION, $input, 'updateCustomerSession');
    }

    /**
     * Retrieves customer recommendations associated with a customer session.
     *
     * Example:
     *
     *   $input = CustomerRecommendationsInput::builder($sessionId, [Recommendations::PAYMENT_RECOMMENDATIONS])
     *      ->build();
     *
     *  $result = $gateway->customerSession()->getCustomerRecommendations($input);
     *
     *  if ($result->success) {
     *    $payload =  $result->customerRecommendations;
     *    print_r("success!: " . $payload->isInPayPalNetwork);
     *  }
     *
     * @param CustomerRecommendationsInput $input The input parameters for retrieving customer recommendations.
     *
     * @return Result\Error|Result\Successful A result object containing customer recommendations if successful, or errors otherwise.
     *
     * @throws Exception\Unexpected If there is an unexpected error during the process.
     */
    public function getCustomerRecommendations(CustomerRecommendationsInput $input)
    {
        $inputMap = $input->toArray();
        $variables = (count($inputMap) == 0) ? ['input' => self::empty()] : ['input' => $inputMap];

        try {
            $response = $this->graphQLClient->query(self::GET_CUSTOMER_RECOMMENDATIONS_QUERY, $variables);
            $errors = GraphQLClient::getValidationErrors($response);
            if ($errors) {
                return new Result\Error(['errors' => $errors]);
            }
            return new Result\Successful($this->extractCustomerRecommendationsPayload($response), "customerRecommendations");
        } catch (\Throwable $e) {
            throw new Exception\Unexpected($e->getMessage());
        }
    }


    private function executeMutation($query, $input, $operationName)
    {
        $inputMap = $input->toArray();
        $variables = (count($inputMap) == 0) ? ['input' => self::empty()] : ['input' => $inputMap];
        try {
            $response = $this->graphQLClient->query($query, $variables);
            $errors = GraphQLClient::getValidationErrors($response);
            if ($errors) {
                return new Result\Error(['errors' => $errors]);
            }

            $sessionId = $this->getValue($response, "data.{$operationName}.sessionId");

            return new Result\Successful($sessionId, "sessionId");
        } catch (Exception $e) {
            throw new Exception\Unexpected($e->getMessage());
        }
    }

    private function getValue($response, $key)
    {
        $map = $response;
        $keyParts = explode(".", $key);
        $lastKeyIndex = count($keyParts) - 1;

        for ($k = 0; $k < $lastKeyIndex; $k++) {
            $subKey = $keyParts[$k];
            $map = $this->popValue($map, $subKey);
        }

        $lastKey = $keyParts[$lastKeyIndex];
        return $this->popValue($map, $lastKey);
    }

    private function popValue($map, $key)
    {
        if (!isset($map[$key])) {
            throw new Exception\Unexpected("Couldn't parse response $key");
        }
        return $map[$key];
    }

    private function extractCustomerRecommendationsPayload($response)
    {
        $isInPayPalNetwork = $this->getValue($response, 'data.customerRecommendations.isInPayPalNetwork');
        $recommendationsMap = $this->getValue($response, 'data.customerRecommendations.recommendations');
        $recommendations = CustomerRecommendations::factory([
            "paymentOptions" => $this->getPaymentOptions($recommendationsMap)
        ]);
        $payload = CustomerRecommendationsPayload::factory([
            "isInPayPalNetwork" => $isInPayPalNetwork,
            "recommendations" => $recommendations
        ]);
        return $payload;
    }

    private function getPaymentOptions($recommendationsMap)
    {
        $paymentOptions = [];
        if ($recommendationsMap == null) {
            return $paymentOptions;
        }
        $paymentOptionsObjs = $this->popValue($recommendationsMap, "paymentOptions");

        foreach ($paymentOptionsObjs as $paymentOptionsObj) {
            $recommendedPriority = $this->popValue($paymentOptionsObj, 'recommendedPriority');
            $paymentOption = $this->popValue($paymentOptionsObj, 'paymentOption');

            $paymentOptions[] = PaymentOptions::factory([
            "paymentOption" => $paymentOption,
            "recommendedPriority" => $recommendedPriority
            ]);
        }

        return $paymentOptions;
    }

    private static function empty()
    {
        return (object)[];
    }

    const CREATE_CUSTOMER_SESSION_MUTATION = <<<'GRAPHQL'
    mutation CreateCustomerSession($input: CreateCustomerSessionInput!) {
      createCustomerSession(input: $input) {
        sessionId
      }
    }
    GRAPHQL;


    const UPDATE_CUSTOMER_SESSION_MUTATION = <<<'GRAPHQL'
    mutation UpdateCustomerSession($input: UpdateCustomerSessionInput!) {
      updateCustomerSession(input: $input) {
        sessionId
      }
    }
    GRAPHQL;

    const GET_CUSTOMER_RECOMMENDATIONS_QUERY = <<<'GRAPHQL'
    query CustomerRecommendations($input: CustomerRecommendationsInput!) {
                customerRecommendations(input: $input) {
                  isInPayPalNetwork
                  recommendations {
                    ... on PaymentRecommendations {
                      paymentOptions {
                        paymentOption
                        recommendedPriority
                      }
                    }
                  }
                }
              }
    GRAPHQL;
}
