<?php
/**
 *
 * CredentialsParser registry
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

class Braintree_CredentialsParser
{
    public static function parseClientCredentials($attribs) {
        $clientIdExploded = explode('$', $attribs['clientId']);
        if (sizeof($clientIdExploded) != 3) {
            throw new Braintree_Exception_Configuration('Incorrect clientId format. Expected: type$environment$token');
        }

        $clientIdConfig = array(
            'wantedType' => 'client_id',
            'gotType' => $clientIdExploded[0],
            'environment' => $clientIdExploded[1],
            'token' => $clientIdExploded[2]
        );

        if ($clientIdConfig['wantedType'] != $clientIdConfig['gotType']) {
            throw new Braintree_Exception_Configuration('Value passed for clientId is not a clientId');
        }

        if (empty($attribs['clientSecret'])) {
            throw new Braintree_Exception_Configuration('clientSecret needs to be set.');
        }
        $clientSecretExploded = explode('$', $attribs['clientSecret']);
        if (sizeof($clientSecretExploded) != 3) {
            throw new Braintree_Exception_Configuration('Incorrect clientSecret format. Expected: type$environment$token');
        }

        $clientSecretConfig = array(
            'wantedType' => 'client_secret',
            'gotType' => $clientSecretExploded[0],
            'environment' => $clientSecretExploded[1],
            'token' => $clientSecretExploded[2]
        );

        if ($clientSecretConfig['wantedType'] != $clientSecretConfig['gotType']) {
            throw new Braintree_Exception_Configuration('Value passed for clientSecret is not a clientSecret');
        }

        if ($clientIdConfig['environment'] != $clientSecretConfig['environment']) {
            throw new Braintree_Exception_Configuration(
                'Mismatched credential environments: clientId environment is ' . $clientIdConfig['environment'].
                ' and clientSecret environment is ' . $clientSecretConfig['environment']);
        }

        return array(
            'environment' => $clientIdConfig['environment'],
            'clientId' => $attribs['clientId'],
            'clientSecret' => $attribs['clientSecret']
        );
    }

    public static function parseAccessToken($accessToken) {
       $accessTokenExploded = explode('$', $accessToken);
        if (sizeof($accessTokenExploded) != 4) {
            throw new Braintree_Exception_Configuration('Incorrect accessToken syntax. Expected: type$environment$merchant_id$token');
        }

        $accessTokenConfig = array(
            'wantedType' => 'access_token',
            'gotType' => $accessTokenExploded[0],
            'environment' => $accessTokenExploded[1],
            'merchantId' => $accessTokenExploded[2],
            'token' => $accessTokenExploded[3]
        );

        if ($accessTokenConfig['wantedType'] != $accessTokenConfig['gotType']) {
            throw new Braintree_Exception_Configuration('Value passed for accessToken is not an accessToken');
        }

        return array(
            'environment' => $accessTokenConfig['environment'],
            'merchantId' => $accessTokenConfig['merchantId'],
            'accessToken' => $accessToken
        );
    }
}

