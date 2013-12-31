<?php

final class Braintree_MerchantAccount extends Braintree
{
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_SUSPENDED = 'suspended';

    const FUNDING_DESTINATION_BANK = 'bank';
    const FUNDING_DESTINATION_EMAIL = 'email';
    const FUNDING_DESTINATION_MOBILE_PHONE = 'mobile_phone';

    public static function create($attribs)
    {
        Braintree_Util::verifyKeys(self::detectSignature($attribs), $attribs);
        return self::_doCreate('/merchant_accounts/create_via_api', array('merchant_account' => $attribs));
    }

    public static function find($merchant_account_id)
    {
        try {
            $response = Braintree_Http::get('/merchant_accounts/' . $merchant_account_id);
            return self::factory($response['merchantAccount']);
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound('merchant account with id ' . $merchant_account_id . ' not found');
        }
    }

    public static function update($merchant_account_id, $attributes)
    {
        Braintree_Util::verifyKeys(self::updateSignature(), $attributes);
        return self::_doUpdate('/merchant_accounts/' . $merchant_account_id . '/update_via_api', array('merchant_account' => $attributes));
    }

    public static function detectSignature($attribs)
    {
        if (isset($attribs['applicantDetails'])) {
            trigger_error("DEPRECATED: Passing applicantDetails to create is deprecated. Please use individual, business, and funding", E_USER_NOTICE);
            return self::createDeprecatedSignature();
        } else {
            return self::createSignature();
        }
    }

    public static function updateSignature()
    {
        $signature = self::createSignature();
        unset($signature['tosAccepted']);
        return $signature;
    }

    public static function createSignature()
    {
        $addressSignature = array('streetAddress', 'postalCode', 'locality', 'region');
        $individualSignature = array(
            'firstName',
            'lastName',
            'email',
            'phone',
            'dateOfBirth',
            'ssn',
            array('address' => $addressSignature)
        );

        $businessSignature = array(
            'dbaName',
            'legalName',
            'taxId',
            array('address' => $addressSignature)
        );

        $fundingSignature = array(
            'routingNumber',
            'accountNumber',
            'destination',
            'email',
            'mobilePhone'
        );

        return array(
            'id',
            'tosAccepted',
            'masterMerchantAccountId',
            array('individual' => $individualSignature),
            array('funding' => $fundingSignature),
            array('business' => $businessSignature)
        );
    }

    public static function createDeprecatedSignature()
    {
        $applicantDetailsAddressSignature = array('streetAddress', 'postalCode', 'locality', 'region');
        $applicantDetailsSignature = array(
            'companyName',
            'firstName',
            'lastName',
            'email',
            'phone',
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

    private static function _doUpdate($url, $params)
    {
        $response = Braintree_Http::put($url, $params);

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

        if (isset($merchantAccountAttribs['individual'])) {
            $individual = $merchantAccountAttribs['individual'];
            $this->_set('individualDetails', Braintree_MerchantAccount_IndividualDetails::Factory($individual));
        }

        if (isset($merchantAccountAttribs['business'])) {
            $business = $merchantAccountAttribs['business'];
            $this->_set('businessDetails', Braintree_MerchantAccount_BusinessDetails::Factory($business));
        }

        if (isset($merchantAccountAttribs['funding'])) {
            $funding = $merchantAccountAttribs['funding'];
            $this->_set('fundingDetails', new Braintree_MerchantAccount_FundingDetails($funding));
        }

        if (isset($merchantAccountAttribs['masterMerchantAccount'])) {
            $masterMerchantAccount = $merchantAccountAttribs['masterMerchantAccount'];
            $this->_set('masterMerchantAccount', Braintree_MerchantAccount::Factory($masterMerchantAccount));
        }
    }
}
