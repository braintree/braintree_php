<?php

final class Braintree_MerchantAccount extends Braintree
{
    const STATUS_ACTIVE  = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_SUSPENDED = 'suspended';

    public static function create($attribs)
    {
        Braintree_Util::verifyKeys(self::createSignature(), $attribs);
        return self::_doCreate('/merchant_accounts/create_via_api', array('merchant_account' => $attribs));
    }

    public static function createSignature()
    {
        $applicantDetailsAddressSignature = array('streetAddress', 'postalCode', 'locality', 'region');
        $applicantDetailsSignature = array(
            'companyName',
            'firstName',
            'lastName',
            'email',
            'dateOfBirth',
            'ssn',
            'taxId',
            'routingNumber',
            'accountNumber',
            array('address' => $applicantDetailsAddressSignature)
        );

        return array(
            array('applicantDetails' =>  $applicantDetailsSignature),
            'id',
            'tosAccepted',
            'masterMerchantAccountId'
        );
    }

    public static function _doCreate($url, $params)
    {
        $response = Braintree_Http::post($url, $params);

        return self::_verifyGatewayResponse($response);
    }

    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['merchantAccount'])) {
            // return a populated instance of Braintree_merchantAccount
            return new Braintree_Result_Successful(
                    self::factory($response['merchantAccount'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Braintree_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Braintree_Exception_Unexpected(
            "Expected merchant account or apiErrorResponse"
            );
        }
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    protected function _initialize($merchantAccountAttribs)
    {
        $this->_attributes = $merchantAccountAttribs;

        if (isset($merchantAccountAttribs['masterMerchantAccount'])) {
          $masterMerchantAccount = $merchantAccountAttribs['masterMerchantAccount'];
          $this->_set('masterMerchantAccount', Braintree_MerchantAccount::Factory($masterMerchantAccount));
        }
    }

}
