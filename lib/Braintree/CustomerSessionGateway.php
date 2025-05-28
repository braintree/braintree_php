<?php

namespace Braintree;

use Braintree\GraphQL\Unions\CustomerRecommendations;
use Braintree\GraphQL\Types\CustomerRecommendationsPayload;
use Braintree\GraphQL\Types\PaymentOptions;
use Braintree\GraphQL\Types\PaymentRecommendation;
use Braintree\GraphQL\Inputs\CreateCustomerSessionInput;
use Braintree\GraphQL\Inputs\UpdateCustomerSessionInput;
use Braintree\GraphQL\Inputs\CustomerRecommendationsInput;

/**
 * Creates and manages PayPal customer sessions.
 *
 * @experimental This class is experimental and may change in future releases.
 */
class CustomerSessionGateway
{
    private $graphQLClient;

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

    const  GENERATE_CUSTOMER_RECOMMENDATIONS_MUTATION = <<<'GRAPHQL'
    mutation GenerateCustomerRecommendations($input: GenerateCustomerRecommendationsInput!) {
        generateCustomerRecommendations(input: $input) {
            sessionId
            isInPayPalNetwork
            paymentRecommendations {
                paymentOption
                recommendedPriority
            }
        }
    }
    GRAPHQL;

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
     * @throws Exception\ServerError If there is an unexpected error during the process.
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
     * @throws Exception\ServerError If there is an unexpected error during the process.
     */
    public function updateCustomerSession($input)
    {
        return $this->executeMutation(self::UPDATE_CUSTOMER_SESSION_MUTATION, $input, 'updateCustomerSession');
    }

    /**
     * Retrieves customer recommendations associated with a customer session. Creates a new session if a session ID is not specified.
     *
     * Example:
     *
     *   $input = CustomerRecommendationsInput::builder()
     *      ->sessionId("1234")
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
     * @throws Exception\ServerError If there is an unexpected error during the process.
     */
    public function getCustomerRecommendations(CustomerRecommendationsInput $input)
    {
        $inputMap = $input->toArray();
        $variables = (count($inputMap) == 0) ? ['input' => self::empty()] : ['input' => $inputMap];

        $response = $this->graphQLClient->query(self::GENERATE_CUSTOMER_RECOMMENDATIONS_MUTATION, $variables);
        $errors = GraphQLClient::getValidationErrors($response);
        if ($errors) {
            return new Result\Error(['errors' => $errors]);
        }
        return new Result\Successful($this->extractCustomerRecommendationsPayload($response), "customerRecommendations");
    }


    private function executeMutation($query, $input, $operationName)
    {
        $inputMap = $input->toArray();
        $variables = (count($inputMap) == 0) ? ['input' => self::empty()] : ['input' => $inputMap];
        $response = $this->graphQLClient->query($query, $variables);
        $errors = GraphQLClient::getValidationErrors($response);
        if ($errors) {
            return new Result\Error(['errors' => $errors]);
        }

        $sessionId = $this->getValue($response, "data.{$operationName}.sessionId");

        return new Result\Successful($sessionId, "sessionId");
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
            throw new Exception\ServerError("Couldn't parse server response");
        }
        return $map[$key];
    }

    private function extractCustomerRecommendationsPayload($response)
    {
        $isInPayPalNetwork = $this->getValue($response, 'data.generateCustomerRecommendations.isInPayPalNetwork');
        $paymentRecommendationsList = $this->getValue($response, 'data.generateCustomerRecommendations.paymentRecommendations');
        $recommendations = CustomerRecommendations::factory([
            "paymentRecommendations" => $this->getPaymentRecommendations($paymentRecommendationsList)
        ]);
        $payload = CustomerRecommendationsPayload::factory([
            "isInPayPalNetwork" => $isInPayPalNetwork,
            "recommendations" => $recommendations
        ]);
        return $payload;
    }

    private function getPaymentRecommendations($recommendationsList)
    {
        $paymentRecommendations = [];
        if ($recommendationsList == null) {
            return $paymentRecommendations;
        }

        foreach ($recommendationsList as $paymentRecommendation) {
            $recommendedPriority = $this->popValue($paymentRecommendation, 'recommendedPriority');
            $paymentOption = $this->popValue($paymentRecommendation, 'paymentOption');

            $paymentRecommendations[] = PaymentRecommendation::factory([
              "paymentOption" => $paymentOption,
              "recommendedPriority" => $recommendedPriority
            ]);
        }

        return $paymentRecommendations;
    }

    private static function empty()
    {
        return (object)[];
    }
}
