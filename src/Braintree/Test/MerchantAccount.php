<?php
namespace Braintree\Test;

/**
 * Merchant Account constants used for testing purposes.
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class MerchantAccount
{
    public static $approve = 'approve_me';

    public static $insufficientFundsContactUs = 'insufficient_funds__contact';
    public static $accountNotAuthorizedContactUs = 'account_not_authorized__contact';
    public static $bankRejectedUpdateFundingInformation = 'bank_rejected__update';
    public static $bankRejectedNone = 'bank_rejected__none';
}
