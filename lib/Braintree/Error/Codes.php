<?php
/**
 * validation Error codes and messages
 *
 * @package    Braintree
 * @subpackage Errors
 * @category   Validation
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 *
 * Validation Error codes and messages
 *
 * ErrorCodes class provides constants for validation errors.
 * The constants should be used to check for a specific validation
 * error in a ValidationErrorCollection.
 * The error messages returned from the server may change,
 * but the codes will remain the same.
 *
 * @package    Braintree
 * @subpackage Errors
 * @category   Validation
 * @copyright  2010 Braintree Payment Solutions
 */
class Braintree_Error_Codes
{
    const ADDRESS_CANNOT_BE_BLANK                      = '81801';
    const ADDRESS_COMPANY_IS_TOO_LONG                  = '81802';
    const ADDRESS_COUNTRY_CODE_ALPHA2_IS_NOT_ACCEPTED  = '91814';
    const ADDRESS_COUNTRY_CODE_ALPHA3_IS_NOT_ACCEPTED  = '91816';
    const ADDRESS_COUNTRY_CODE_NUMERIC_IS_NOT_ACCEPTED = '91817';
    const ADDRESS_COUNTRY_NAME_IS_NOT_ACCEPTED         = '91803';
    const ADDRESS_EXTENDED_ADDRESS_IS_TOO_LONG         = '81804';
    const ADDRESS_FIRST_NAME_IS_TOO_LONG               = '81805';
    const ADDRESS_INCONSISTENT_COUNTRY                 = '91815';
    const ADDRESS_LAST_NAME_IS_TOO_LONG                = '81806';
    const ADDRESS_LOCALITY_IS_TOO_LONG                 = '81807';
    const ADDRESS_POSTAL_CODE_IS_REQUIRED              = '81808';
    const ADDRESS_POSTAL_CODE_INVALID_CHARACTERS       = '81813';
    const ADDRESS_POSTAL_CODE_IS_TOO_LONG              = '81809';
    const ADDRESS_REGION_IS_TOO_LONG                   = '81810';
    const ADDRESS_STREET_ADDRESS_IS_REQUIRED           = '81811';
    const ADDRESS_STREET_ADDRESS_IS_TOO_LONG           = '81812';

    const CREDIT_CARD_BILLING_ADDRESS_CONFLICT                                          = '91701';
    const CREDIT_CARD_BILLING_ADDRESS_ID_IS_INVALID                                     = '91702';
    const CREDIT_CARD_CARDHOLDER_NAME_IS_TOO_LONG                                       = '81723';
    const CREDIT_CARD_CREDIT_CARD_TYPE_IS_NOT_ACCEPTED                                  = '81703';
    const CREDIT_CARD_CREDIT_CARD_TYPE_IS_NOT_ACCEPTED_BY_SUBSCRIPTION_MERCHANT_ACCOUNT = "81718";
    const CREDIT_CARD_CUSTOMER_ID_IS_INVALID                                            = '91705';
    const CREDIT_CARD_CUSTOMER_ID_IS_REQUIRED                                           = '91704';
    const CREDIT_CARD_CVV_IS_INVALID                                                    = '81707';
    const CREDIT_CARD_CVV_IS_REQUIRED                                                   = '81706';
    const CREDIT_CARD_EXPIRATION_DATE_CONFLICT                                          = '91708';
    const CREDIT_CARD_EXPIRATION_DATE_IS_INVALID                                        = '81710';
    const CREDIT_CARD_EXPIRATION_DATE_IS_REQUIRED                                       = '81709';
    const CREDIT_CARD_EXPIRATION_DATE_YEAR_IS_INVALID                                   = '81711';
    const CREDIT_CARD_EXPIRATION_MONTH_IS_INVALID                                       = '81712';
    const CREDIT_CARD_EXPIRATION_YEAR_IS_INVALID                                        = '81713';
    const CREDIT_CARD_NUMBER_INVALID_LENGTH                                             = '81716';
    const CREDIT_CARD_NUMBER_IS_INVALID                                                 = '81715';
    const CREDIT_CARD_NUMBER_IS_REQUIRED                                                = '81714';
    const CREDIT_CARD_NUMBER_MUST_BE_TEST_NUMBER                                        = '81717';
    const CREDIT_CARD_OPTIONS_UPDATE_EXISTING_TOKEN_IS_INVALID                          = '91723';
    const CREDIT_CARD_TOKEN_INVALID                                                     = '91718';
    const CREDIT_CARD_TOKEN_IS_IN_USE                                                   = '91719';
    const CREDIT_CARD_TOKEN_IS_NOT_ALLOWED                                              = '91721';
    const CREDIT_CARD_TOKEN_IS_REQUIRED                                                 = '91722';
    const CREDIT_CARD_TOKEN_IS_TOO_LONG                                                 = '91720';

    const CUSTOMER_COMPANY_IS_TOO_LONG      = '81601';
    const CUSTOMER_CUSTOM_FIELD_IS_INVALID  = '91602';
    const CUSTOMER_CUSTOM_FIELD_IS_TOO_LONG = '81603';
    const CUSTOMER_EMAIL_IS_INVALID         = '81604';
    const CUSTOMER_EMAIL_IS_REQUIRED        = '81606';
    const CUSTOMER_EMAIL_IS_TOO_LONG        = '81605';
    const CUSTOMER_FAX_IS_TOO_LONG          = '81607';
    const CUSTOMER_FIRST_NAME_IS_TOO_LONG   = '81608';
    const CUSTOMER_ID_IS_INVAILD            = '91610';
    const CUSTOMER_ID_IS_IN_USE             = '91609';
    const CUSTOMER_ID_IS_NOT_ALLOWED        = '91611';
    const CUSTOMER_ID_IS_REQUIRED           = '91613';
    const CUSTOMER_ID_IS_TOO_LONG           = '91612';
    const CUSTOMER_LAST_NAME_IS_TOO_LONG    = '81613';
    const CUSTOMER_PHONE_IS_TOO_LONG        = '81614';
    const CUSTOMER_WEBSITE_IS_INVALID       = '81616';
    const CUSTOMER_WEBSITE_IS_TOO_LONG      = '81615';

    const SUBSCRIPTION_CANNOT_EDIT_CANCELED_SUBSCRIPTION                  = '81901';
    const SUBSCRIPTION_ID_IS_IN_USE                                       = '81902';
    const SUBSCRIPTION_MERCHANT_ACCOUNT_ID_IS_INVALID                     = '91901';
    const SUBSCRIPTION_PAYMENT_METHOD_TOKEN_CARD_TYPE_IS_NOT_ACCEPTED     = "91902";
    const SUBSCRIPTION_PAYMENT_METHOD_TOKEN_IS_INVALID                    = "91903";
    const SUBSCRIPTION_PAYMENT_METHOD_TOKEN_NOT_ASSOCIATED_WITH_CUSTOMER  = "91905";
    const SUBSCRIPTION_PLAN_ID_IS_INVALID                                 = "91904";
    const SUBSCRIPTION_PRICE_CANNOT_BE_BLANK                              = '81903';
    const SUBSCRIPTION_PRICE_FORMAT_IS_INVALID                            = '81904';
    const SUBSCRIPTION_STATUS_IS_CANCELED                                 = '81905';
    const SUBSCRIPTION_TOKEN_FORMAT_IS_INVALID                            = '81906';
    const SUBSCRIPTION_TRIAL_DURATION_FORMAT_IS_INVALID                   = '81907';
    const SUBSCRIPTION_TRIAL_DURATION_IS_REQUIRED                         = '81908';
    const SUBSCRIPTION_TRIAL_DURATION_UNIT_IS_INVALID                     = '81909';
    const SUBSCRIPTION_NUMBER_OF_BILLING_CYCLES_IS_TOO_SMALL              = '91909';
    const SUBSCRIPTION_CANNOT_EDIT_EXPIRED_SUBSCRIPTION                   = '81910';
    const SUBSCRIPTION_NUMBER_OF_BILLING_CYCLES_MUST_BE_GREATER_THAN_ZERO = '91907';
    const SUBSCRIPTION_NUMBER_OF_BILLING_CYCLES_MUST_BE_NUMERIC           = '91906';
    const SUBSCRIPTION_INCONSISTENT_NUMBER_OF_BILLING_CYCLES              = '91908';

    const TRANSACTION_AMOUNT_CANNOT_BE_NEGATIVE                                 = '81501';
    const TRANSACTION_AMOUNT_IS_REQUIRED                                        = '81502';
    const TRANSACTION_AMOUNT_IS_INVALID                                         = '81503';
    const TRANSACTION_AMOUNT_IS_TOO_LARGE                                       = '81528';
    const TRANSACTION_BILLING_ADDRESS_CONFLICT                                  = '91530';
    const TRANSACTION_CANNOT_BE_VOIDED                                          = '91504';
    const TRANSACTION_CANNOT_REFUND_CREDIT                                      = '91505';
    const TRANSACTION_CANNOT_REFUND_UNLESS_SETTLED                              = '91506';
    const TRANSACTION_CANNOT_SUBMIT_FOR_SETTLEMENT                              = '91507';
    const TRANSACTION_CREDIT_CARD_IS_REQUIRED                                   = '91508';
    const TRANSACTION_CUSTOM_FIELD_IS_TOO_LONG                                  = '81527';
    const TRANSACTION_CUSTOMER_DEFAULT_PAYMENT_METHOD_CARD_TYPE_IS_NOT_ACCEPTED = '81509';
    const TRANSACTION_CUSTOMER_ID_IS_INVALID                                    = '91510';
    const TRANSACTION_CUSTOMER_DOES_NOT_HAVE_CREDIT_CARD                        = '91511';
    const TRANSACTION_HAS_ALREADY_BEEN_REFUNDED                                 = '91512';
    const TRANSACTION_MERCHANT_ACCOUNT_NAME_IS_INVALID                          = '91513';
    const TRANSACTION_MERCHANT_ACCOUNT_IS_SUSPENDED                             = '91514';
    const TRANSACTION_ORDER_ID_IS_TOO_LONG                                      = '91501';
    const TRANSACTION_PAYMENT_METHOD_DOES_NOT_BELONG_TO_SUBSCRIPTION            = '91527';
    const TRANSACTION_SUBSCRIPTION_ID_IS_INVALID                                = '91528';
    const TRANSACTION_PAYMENT_METHOD_CONFLICT                                   = '91515';
    const TRANSACTION_PAYMENT_METHOD_DOES_NOT_BELONG_TO_CUSTOMER                = '91516';
    const TRANSACTION_PAYMENT_METHOD_TOKEN_CARD_TYPE_IS_NOT_ACCEPTED            = '91517';
    const TRANSACTION_PAYMENT_METHOD_TOKEN_IS_INVALID                           = '91518';
    const TRANSACTION_PROCESSOR_AUTHORIZATION_CODE_CANNOT_BE_SET                = '91519';
    const TRANSACTION_PROCESSOR_AUTHORIZATION_CODE_IS_INVALID                   = '81520';
    const TRANSACTION_REFUND_AMOUNT_IS_TOO_LARGE                                = '91521';
    const TRANSACTION_SETTLEMENT_AMOUNT_IS_TOO_LARGE                            = '91522';
    const TRANSACTION_SUBSCRIPTION_DOES_NOT_BELONG_TO_CUSTOMER                  = '91529';
    const TRANSACTION_TYPE_IS_INVALID                                           = '91523';
    const TRANSACTION_TYPE_IS_REQUIRED                                          = '91524';
    const TRANSACTION_OPTIONS_VAULT_IS_DISABLED                                 = '91525';
    const TRANSACTION_CUSTOM_FIELD_IS_INVALID                                   = '91526';
}
