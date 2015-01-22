<?php
/**
 * Braintree OAuthGateway module
 * PHP Version 5
 * Creates and manages Braintree Addresses
 *
 * @package   Braintree
 * @copyright 2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_OAuthGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_http = new Braintree_Http($gateway->config);
    }

    public function createAccessToken($params)
    {
        $this->_config->assertHasClientCredentials();

        $response = $this->_http->post('/oauth/access_tokens', array('accessToken' => $params));
        return $this->_verifyGatewayResponse($response);
    }

    private function _verifyGatewayResponse($response)
    {
        if (isset($response['credentials'])) {
            if (isset($response['credentials']['error'])) {
                // TODO: Fix Error Handling
                // $errors = array('errors' => array($response['accessToken']));
                // return new Braintree_Result_Error($errors);
                $failure = new Braintree_Result_Successful(
                        Braintree_OAuthCredentials::factory($response['credentials'])
                );
                $failure->success = false;
                return $failure;
            } else {
                return new Braintree_Result_Successful(
                        Braintree_OAuthCredentials::factory($response['credentials'])
                );
            }
        } else {
            throw new Braintree_Exception_Unexpected(
                "Expected credentials or apiErrorResponse"
            );
        }
    }

    public function connectUrl($params = array())
    {
        $this->_config->assertHasClientId();

        $query = array('client_id' => $this->_config->getClientId());
        if (isset($params['merchantId'])) {
            $query['merchant_id'] = $params['merchantId'];
        }
        if (isset($params['redirectUri'])) {
            $query['redirect_uri'] = $params['redirectUri'];
        }
        if (isset($params['scope'])) {
            $query['scope'] = $params['scope'];
        }
        if (isset($params['state'])) {
            $query['state'] = $params['state'];
        }
        if (isset($params['user'])) {
            $query['user'] = array();
            if (isset($params['user']['country'])) {
                $query['user']['country'] = $params['user']['country'];
            }
            if (isset($params['user']['email'])) {
                $query['user']['email'] = $params['user']['email'];
            }
            if (isset($params['user']['firstName'])) {
                $query['user']['first_name'] = $params['user']['firstName'];
            }
            if (isset($params['user']['lastName'])) {
                $query['user']['last_name'] = $params['user']['lastName'];
            }
            if (isset($params['user']['phone'])) {
                $query['user']['phone'] = $params['user']['phone'];
            }
            if (isset($params['user']['dobYear'])) {
                $query['user']['dob_year'] = $params['user']['dobYear'];
            }
            if (isset($params['user']['dobMonth'])) {
                $query['user']['dob_month'] = $params['user']['dobMonth'];
            }
            if (isset($params['user']['dobDay'])) {
                $query['user']['dob_day'] = $params['user']['dobDay'];
            }
            if (isset($params['user']['streetAddress'])) {
                $query['user']['street_address'] = $params['user']['streetAddress'];
            }
            if (isset($params['user']['locality'])) {
                $query['user']['locality'] = $params['user']['locality'];
            }
            if (isset($params['user']['region'])) {
                $query['user']['region'] = $params['user']['region'];
            }
            if (isset($params['user']['postalCode'])) {
                $query['user']['postal_code'] = $params['user']['postalCode'];
            }
            if (isset($params['user']['website'])) {
                $query['user']['website'] = $params['user']['website'];
            }
        }
        if (isset($params['business'])) {
            $query['business'] = array();
            if (isset($params['business']['name'])) {
                $query['business']['name'] = $params['business']['name'];
            }
            if (isset($params['business']['registeredAs'])) {
                $query['business']['registered_as'] = $params['business']['registeredAs'];
            }
            if (isset($params['business']['industry'])) {
                $query['business']['industry'] = $params['business']['industry'];
            }
            if (isset($params['business']['description'])) {
                $query['business']['description'] = $params['business']['description'];
            }
            if (isset($params['business']['streetAddress'])) {
                $query['business']['street_address'] = $params['business']['streetAddress'];
            }
            if (isset($params['business']['locality'])) {
                $query['business']['locality'] = $params['business']['locality'];
            }
            if (isset($params['business']['region'])) {
                $query['business']['region'] = $params['business']['region'];
            }
            if (isset($params['business']['postalCode'])) {
                $query['business']['postal_code'] = $params['business']['postalCode'];
            }
            if (isset($params['business']['country'])) {
                $query['business']['country'] = $params['business']['country'];
            }
            if (isset($params['business']['annualVolumeAmount'])) {
                $query['business']['annual_volume_amount'] = $params['business']['annualVolumeAmount'];
            }
            if (isset($params['business']['averageTransactionAmount'])) {
                $query['business']['average_transaction_amount'] = $params['business']['averageTransactionAmount'];
            }
            if (isset($params['business']['maximumTransactionAmount'])) {
                $query['business']['maximum_transaction_amount'] = $params['business']['maximumTransactionAmount'];
            }
            if (isset($params['business']['shipPhysicalGoods'])) {
                $query['business']['ship_physical_goods'] = $params['business']['shipPhysicalGoods'];
            }
            if (isset($params['business']['fulfillmentCompletedIn'])) {
                $query['business']['fulfillment_completed_in'] = $params['business']['fulfillmentCompletedIn'];
            }
            if (isset($params['business']['currency'])) {
                $query['business']['currency'] = $params['business']['currency'];
            }
        }
        return $this->_config->baseUrl() . '/oauth/connect?' . http_build_query($query);
    }
}
