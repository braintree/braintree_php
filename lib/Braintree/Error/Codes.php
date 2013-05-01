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
 * The error messages returned from the server may change;
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
    const ADDRESS_POSTAL_CODE_INVALID_CHARACTERS       = '81813';
    const ADDRESS_POSTAL_CODE_IS_REQUIRED              = '81808';
    const ADDRESS_POSTAL_CODE_IS_TOO_LONG              = '81809';
    const ADDRESS_REGION_IS_TOO_LONG                   = '81810';
    const ADDRESS_STREET_ADDRESS_IS_REQUIRED           = '81811';
    const ADDRESS_STREET_ADDRESS_IS_TOO_LONG           = '81812';
    const ADDRESS_TOO_MANY_ADDRESSES_PER_CUSTOMER      = '91818';
    const ADDRESS_COMPANY_IS_INVALID                   = '91821';
    const ADDRESS_REGION_IS_INVALID                    = '91825';
    const ADDRESS_POSTAL_CODE_IS_INVALID               = '91826';
    const ADDRESS_LAST_NAME_IS_INVALID                 = '91820';
    const ADDRESS_EXTENDED_ADDRESS_IS_INVALID          = '91823';
    const ADDRESS_STREET_ADDRESS_IS_INVALID            = '91822';
    const ADDRESS_LOCALITY_IS_INVALID                  = '91824';
    const ADDRESS_FIRST_NAME_IS_INVALID                = '91819';

    const CREDIT_CARD_BILLING_ADDRESS_CONFLICT                                          = '91701';
    const CREDIT_CARD_BILLING_ADDRESS_ID_IS_INVALID                                     = '91702';
    const CREDIT_CARD_CARDHOLDER_NAME_IS_TOO_LONG                                       = '81723';
    const CREDIT_CARD_CREDIT_CARD_TYPE_IS_NOT_ACCEPTED                                  = '81703';
    const CREDIT_CARD_CREDIT_CARD_TYPE_IS_NOT_ACCEPTED_BY_SUBSCRIPTION_MERCHANT_ACCOUNT = '81718';
    const CREDIT_CARD_CUSTOMER_ID_IS_INVALID                                            = '91705';
    const CREDIT_CARD_CUSTOMER_ID_IS_REQUIRED                                           = '91704';
    const CREDIT_CARD_CVV_IS_INVALID                                                    = '81707';
    const CREDIT_CARD_CVV_IS_REQUIRED                                                   = '81706';
    const CREDIT_CARD_DUPLICATE_CARD_EXISTS                                             = '81724';
    const CREDIT_CARD_EXPIRATION_DATE_CONFLICT                                          = '91708';
    const CREDIT_CARD_EXPIRATION_DATE_IS_INVALID                                        = '81710';
    const CREDIT_CARD_EXPIRATION_DATE_IS_REQUIRED                                       = '81709';
    const CREDIT_CARD_EXPIRATION_DATE_YEAR_IS_INVALID                                   = '81711';
    const CREDIT_CARD_EXPIRATION_MONTH_IS_INVALID                                       = '81712';
    const CREDIT_CARD_EXPIRATION_YEAR_IS_INVALID                                        = '81713';
    const CREDIT_CARD_INVALID_VENMO_SDK_PAYMENT_METHOD_CODE                             = '91727';
    const CREDIT_CARD_NUMBER_INVALID_LENGTH                                             = '81716';
    const CREDIT_CARD_NUMBER_IS_INVALID                                                 = '81715';
    const CREDIT_CARD_NUMBER_IS_REQUIRED                                                = '81714';
    const CREDIT_CARD_NUMBER_LENGTH_IS_INVALID                                          = '81716';
    const CREDIT_CARD_NUMBER_MUST_BE_TEST_NUMBER                                        = '81717';
    const CREDIT_CARD_OPTIONS_UPDATE_EXISTING_TOKEN_IS_INVALID                          = '91723';
    const CREDIT_CARD_OPTIONS_VERIFICATION_MERCHANT_ACCOUNT_ID_IS_INVALID               = '91728';
    const CREDIT_CARD_PAYMENT_METHOD_CONFLICT                                           = '81725';
    const CREDIT_CARD_TOKEN_FORMAT_IS_INVALID                                           = '91718';
    const CREDIT_CARD_TOKEN_INVALID                                                     = '91718';
    const CREDIT_CARD_TOKEN_IS_IN_USE                                                   = '91719';
    const CREDIT_CARD_TOKEN_IS_NOT_ALLOWED                                              = '91721';
    const CREDIT_CARD_TOKEN_IS_REQUIRED                                                 = '91722';
    const CREDIT_CARD_TOKEN_IS_TOO_LONG                                                 = '91720';
    const CREDIT_CARD_VENMO_SDK_PAYMENT_METHOD_CODE_CARD_TYPE_IS_NOT_ACCEPTED           = '91726';

    const CUSTOMER_COMPANY_IS_TOO_LONG       = '81601';
    const CUSTOMER_CUSTOM_FIELD_IS_INVALID   = '91602';
    const CUSTOMER_CUSTOM_FIELD_IS_TOO_LONG  = '81603';
    const CUSTOMER_EMAIL_IS_INVALID          = '81604';
    const CUSTOMER_EMAIL_FORMAT_IS_INVALID   = '81604';
    const CUSTOMER_EMAIL_IS_REQUIRED         = '81606';
    const CUSTOMER_EMAIL_IS_TOO_LONG         = '81605';
    const CUSTOMER_FAX_IS_TOO_LONG           = '81607';
    const CUSTOMER_FIRST_NAME_IS_TOO_LONG    = '81608';
    const CUSTOMER_ID_IS_INVAILD             = '91610'; //Deprecated
    const CUSTOMER_ID_IS_INVALID             = '91610';
    const CUSTOMER_ID_IS_IN_USE              = '91609';
    const CUSTOMER_ID_IS_NOT_ALLOWED         = '91611';
    const CUSTOMER_ID_IS_REQUIRED            = '91613';
    const CUSTOMER_ID_IS_TOO_LONG            = '91612';
    const CUSTOMER_LAST_NAME_IS_TOO_LONG     = '81613';
    const CUSTOMER_PHONE_IS_TOO_LONG         = '81614';
    const CUSTOMER_WEBSITE_IS_INVALID        = '81616';
    const CUSTOMER_WEBSITE_FORMAT_IS_INVALID = '81616';
    const CUSTOMER_WEBSITE_IS_TOO_LONG       = '81615';

    const DESCRIPTOR_NAME_FORMAT_IS_INVALID                = '92201';
    const DESCRIPTOR_PHONE_FORMAT_IS_INVALID               = '92202';
    const DESCRIPTOR_INTERNATIONAL_NAME_FORMAT_IS_INVALID  = '92204';
    const DESCRIPTOR_DYNAMIC_DESCRIPTORS_DISABLED          = '92203';
    const DESCRIPTOR_INTERNATIONAL_PHONE_FORMAT_IS_INVALID = '92205';

    const SETTLEMENT_BATCH_SUMMARY_SETTLEMENT_DATE_IS_INVALID = '82302';
    const SETTLEMENT_BATCH_SUMMARY_SETTLEMENT_DATE_IS_REQUIRED = '82301';
    const SETTLEMENT_BATCH_SUMMARY_CUSTOM_FIELD_IS_INVALID = '82303';

	const SUBSCRIPTION_BILLING_DAY_OF_MONTH_CANNOT_BE_UPDATED                     = '91918';
	const SUBSCRIPTION_BILLING_DAY_OF_MONTH_IS_INVALID                            = '91914';
	const SUBSCRIPTION_BILLING_DAY_OF_MONTH_MUST_BE_NUMERIC                       = '91913';
	const SUBSCRIPTION_CANNOT_ADD_DUPLICATE_ADDON_OR_DISCOUNT                     = '91911';
	const SUBSCRIPTION_CANNOT_EDIT_CANCELED_SUBSCRIPTION                          = '81901';
	const SUBSCRIPTION_CANNOT_EDIT_EXPIRED_SUBSCRIPTION                           = '81910';
	const SUBSCRIPTION_CANNOT_EDIT_PRICE_CHANGING_FIELDS_ON_PAST_DUE_SUBSCRIPTION = '91920';
	const SUBSCRIPTION_FIRST_BILLING_DATE_CANNOT_BE_IN_THE_PAST                   = '91916';
	const SUBSCRIPTION_FIRST_BILLING_DATE_CANNOT_BE_UPDATED                       = '91919';
	const SUBSCRIPTION_FIRST_BILLING_DATE_IS_INVALID                              = '91915';
	const SUBSCRIPTION_ID_IS_IN_USE                                               = '81902';
	const SUBSCRIPTION_INCONSISTENT_NUMBER_OF_BILLING_CYCLES                      = '91908';
	const SUBSCRIPTION_INCONSISTENT_START_DATE                                    = '91917';
	const SUBSCRIPTION_INVALID_REQUEST_FORMAT                                     = '91921';
	const SUBSCRIPTION_MERCHANT_ACCOUNT_ID_IS_INVALID                             = '91901';
	const SUBSCRIPTION_MISMATCH_CURRENCY_ISO_CODE                                 = '91923';
	const SUBSCRIPTION_NUMBER_OF_BILLING_CYCLES_CANNOT_BE_BLANK                   = '91912';
	const SUBSCRIPTION_NUMBER_OF_BILLING_CYCLES_IS_TOO_SMALL                      = '91909';
	const SUBSCRIPTION_NUMBER_OF_BILLING_CYCLES_MUST_BE_GREATER_THAN_ZERO         = '91907';
	const SUBSCRIPTION_NUMBER_OF_BILLING_CYCLES_MUST_BE_NUMERIC                   = '91906';
	const SUBSCRIPTION_PAYMENT_METHOD_TOKEN_CARD_TYPE_IS_NOT_ACCEPTED             = '91902';
	const SUBSCRIPTION_PAYMENT_METHOD_TOKEN_IS_INVALID                            = '91903';
	const SUBSCRIPTION_PAYMENT_METHOD_TOKEN_NOT_ASSOCIATED_WITH_CUSTOMER          = '91905';
	const SUBSCRIPTION_PLAN_BILLING_FREQUENCY_CANNOT_BE_UPDATED                   = '91922';
	const SUBSCRIPTION_PLAN_ID_IS_INVALID                                         = '91904';
	const SUBSCRIPTION_PRICE_CANNOT_BE_BLANK                                      = '81903';
	const SUBSCRIPTION_PRICE_FORMAT_IS_INVALID                                    = '81904';
	const SUBSCRIPTION_PRICE_IS_TOO_LARGE                                         = '81923';
	const SUBSCRIPTION_STATUS_IS_CANCELED                                         = '81905';
	const SUBSCRIPTION_TOKEN_FORMAT_IS_INVALID                                    = '81906';
	const SUBSCRIPTION_TRIAL_DURATION_FORMAT_IS_INVALID                           = '81907';
	const SUBSCRIPTION_TRIAL_DURATION_IS_REQUIRED                                 = '81908';
	const SUBSCRIPTION_TRIAL_DURATION_UNIT_IS_INVALID                             = '81909';

    const SUBSCRIPTION_MODIFICATION_AMOUNT_CANNOT_BE_BLANK                             = '92003';
    const SUBSCRIPTION_MODIFICATION_AMOUNT_IS_INVALID                                  = '92002';
    const SUBSCRIPTION_MODIFICATION_AMOUNT_IS_TOO_LARGE                                = '92023';
    const SUBSCRIPTION_MODIFICATION_CANNOT_EDIT_MODIFICATIONS_ON_PAST_DUE_SUBSCRIPTION = '92022';
    const SUBSCRIPTION_MODIFICATION_CANNOT_UPDATE_AND_REMOVE                           = '92015';
    const SUBSCRIPTION_MODIFICATION_EXISTING_ID_IS_INCORRECT_KIND                      = '92020';
    const SUBSCRIPTION_MODIFICATION_EXISTING_ID_IS_INVALID                             = '92011';
    const SUBSCRIPTION_MODIFICATION_EXISTING_ID_IS_REQUIRED                            = '92012';
    const SUBSCRIPTION_MODIFICATION_ID_TO_REMOVE_IS_INCORRECT_KIND                     = '92021';
    const SUBSCRIPTION_MODIFICATION_ID_TO_REMOVE_IS_NOT_PRESENT                        = '92016';
    const SUBSCRIPTION_MODIFICATION_INCONSISTENT_NUMBER_OF_BILLING_CYCLES              = '92018';
    const SUBSCRIPTION_MODIFICATION_INHERITED_FROM_ID_IS_INVALID                       = '92013';
    const SUBSCRIPTION_MODIFICATION_INHERITED_FROM_ID_IS_REQUIRED                      = '92014';
    const SUBSCRIPTION_MODIFICATION_MISSING                                            = '92024';
    const SUBSCRIPTION_MODIFICATION_NUMBER_OF_BILLING_CYCLES_CANNOT_BE_BLANK           = '92017';
    const SUBSCRIPTION_MODIFICATION_NUMBER_OF_BILLING_CYCLES_IS_INVALID                = '92005';
    const SUBSCRIPTION_MODIFICATION_NUMBER_OF_BILLING_CYCLES_MUST_BE_GREATER_THAN_ZERO = '92019';
    const SUBSCRIPTION_MODIFICATION_QUANTITY_CANNOT_BE_BLANK                           = '92004';
    const SUBSCRIPTION_MODIFICATION_QUANTITY_IS_INVALID                                = '92001';
    const SUBSCRIPTION_MODIFICATION_QUANTITY_MUST_BE_GREATER_THAN_ZERO                 = '92010';

    const TRANSACTION_AMOUNT_CANNOT_BE_NEGATIVE                                 = '81501';
    const TRANSACTION_AMOUNT_FORMAT_IS_INVALID                                  = '81503';
    const TRANSACTION_AMOUNT_IS_INVALID                                         = '81503';
    const TRANSACTION_AMOUNT_IS_REQUIRED                                        = '81502';
    const TRANSACTION_AMOUNT_IS_TOO_LARGE                                       = '81528';
    const TRANSACTION_AMOUNT_MUST_BE_GREATER_THAN_ZERO                          = '81531';
    const TRANSACTION_BILLING_ADDRESS_CONFLICT                                  = '91530';
    const TRANSACTION_CANNOT_BE_VOIDED                                          = '91504';
    const TRANSACTION_CANNOT_CLONE_CREDIT                                       = '91543';
    const TRANSACTION_CANNOT_CLONE_TRANSACTION_WITH_VAULT_CREDIT_CARD           = '91540';
    const TRANSACTION_CANNOT_CLONE_UNSUCCESSFUL_TRANSACTION                     = '91542';
    const TRANSACTION_CANNOT_CLONE_VOICE_AUTHORIZATIONS                         = '91541';
    const TRANSACTION_CANNOT_REFUND_CREDIT                                      = '91505';
    const TRANSACTION_CANNOT_REFUND_UNLESS_SETTLED                              = '91506';
    const TRANSACTION_CANNOT_REFUND_WITH_SUSPENDED_MERCHANT_ACCOUNT             = '91538';
    const TRANSACTION_CANNOT_SUBMIT_FOR_SETTLEMENT                              = '91507';
    const TRANSACTION_CHANNEL_IS_TOO_LONG                                       = '91550';
    const TRANSACTION_CREDIT_CARD_IS_REQUIRED                                   = '91508';
    const TRANSACTION_CUSTOMER_DEFAULT_PAYMENT_METHOD_CARD_TYPE_IS_NOT_ACCEPTED = '81509';
    const TRANSACTION_CUSTOMER_DOES_NOT_HAVE_CREDIT_CARD                        = '91511';
    const TRANSACTION_CUSTOMER_ID_IS_INVALID                                    = '91510';
    const TRANSACTION_CUSTOM_FIELD_IS_INVALID                                   = '91526';
    const TRANSACTION_CUSTOM_FIELD_IS_TOO_LONG                                  = '81527';
    const TRANSACTION_HAS_ALREADY_BEEN_REFUNDED                                 = '91512';
    const TRANSACTION_MERCHANT_ACCOUNT_DOES_NOT_SUPPORT_REFUNDS                 = '91547';
    const TRANSACTION_MERCHANT_ACCOUNT_ID_IS_INVALID                            = '91513';
    const TRANSACTION_MERCHANT_ACCOUNT_IS_SUSPENDED                             = '91514';
    const TRANSACTION_MERCHANT_ACCOUNT_NAME_IS_INVALID                          = '91513'; //Deprecated
    const TRANSACTION_OPTIONS_SUBMIT_FOR_SETTLEMENT_IS_REQUIRED_FOR_CLONING     = '91544';
    const TRANSACTION_OPTIONS_VAULT_IS_DISABLED                                 = '91525';
    const TRANSACTION_ORDER_ID_IS_TOO_LONG                                      = '91501';
    const TRANSACTION_PAYMENT_METHOD_CONFLICT                                   = '91515';
    const TRANSACTION_PAYMENT_METHOD_CONFLICT_WITH_VENMO_SDK                    = '91549';
    const TRANSACTION_PAYMENT_METHOD_DOES_NOT_BELONG_TO_CUSTOMER                = '91516';
    const TRANSACTION_PAYMENT_METHOD_DOES_NOT_BELONG_TO_SUBSCRIPTION            = '91527';
    const TRANSACTION_PAYMENT_METHOD_TOKEN_CARD_TYPE_IS_NOT_ACCEPTED            = '91517';
    const TRANSACTION_PAYMENT_METHOD_TOKEN_IS_INVALID                           = '91518';
    const TRANSACTION_PROCESSOR_AUTHORIZATION_CODE_CANNOT_BE_SET                = '91519';
    const TRANSACTION_PROCESSOR_AUTHORIZATION_CODE_IS_INVALID                   = '81520';
    const TRANSACTION_PROCESSOR_DOES_NOT_SUPPORT_CREDITS                        = '91546';
    const TRANSACTION_PROCESSOR_DOES_NOT_SUPPORT_VOICE_AUTHORIZATIONS           = '91545';
    const TRANSACTION_PURCHASE_ORDER_NUMBER_IS_INVALID                          = '91548';
    const TRANSACTION_PURCHASE_ORDER_NUMBER_IS_TOO_LONG                         = '91537';
    const TRANSACTION_REFUND_AMOUNT_IS_TOO_LARGE                                = '91521';
    const TRANSACTION_SETTLEMENT_AMOUNT_IS_TOO_LARGE                            = '91522';
    const TRANSACTION_SUBSCRIPTION_DOES_NOT_BELONG_TO_CUSTOMER                  = '91529';
    const TRANSACTION_SUBSCRIPTION_ID_IS_INVALID                                = '91528';
    const TRANSACTION_SUBSCRIPTION_STATUS_MUST_BE_PAST_DUE                      = '91531';
    const TRANSACTION_TAX_AMOUNT_CANNOT_BE_NEGATIVE                             = '81534';
    const TRANSACTION_TAX_AMOUNT_FORMAT_IS_INVALID                              = '81535';
    const TRANSACTION_TAX_AMOUNT_IS_TOO_LARGE                                   = '81536';
    const TRANSACTION_TYPE_IS_INVALID                                           = '91523';
    const TRANSACTION_TYPE_IS_REQUIRED                                          = '91524';
    const TRANSACTION_UNSUPPORTED_VOICE_AUTHORIZATION                           = '91539';
}
