<?php
/**
 * Braintree PHP Library
 * Creates class_aliases for old class names replaced by PSR-4 Namespaces
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

require_once('autoload.php');

class_alias('Braintree\Modification', 'Braintree_Modification');
class_alias('Braintree\Instance', 'Braintree_Instance');
class_alias('Braintree\OAuthCredentials', 'Braintree_OAuthCredentials');
class_alias('Braintree\Address', 'Braintree_Address');
class_alias('Braintree\AddressGateway', 'Braintree_AddressGateway');
class_alias('Braintree\AddOn', 'Braintree_AddOn');
class_alias('Braintree\AddOnGateway', 'Braintree_AddOnGateway');
class_alias('Braintree\AndroidPayCard', 'Braintree_AndroidPayCard');
class_alias('Braintree\ApplePayCard', 'Braintree_ApplePayCard');
class_alias('Braintree\ClientToken', 'Braintree_ClientToken');
class_alias('Braintree\ClientTokenGateway', 'Braintree_ClientTokenGateway');
class_alias('Braintree\CoinbaseAccount', 'Braintree_CoinbaseAccount');
class_alias('Braintree\Collection', 'Braintree_Collection');
class_alias('Braintree\Configuration', 'Braintree_Configuration');
class_alias('Braintree\CredentialsParser', 'Braintree_CredentialsParser');
class_alias('Braintree\CreditCard', 'Braintree_CreditCard');
class_alias('Braintree\CreditCardGateway', 'Braintree_CreditCardGateway');
class_alias('Braintree\Customer', 'Braintree_Customer');
class_alias('Braintree\CustomerGateway', 'Braintree_CustomerGateway');
class_alias('Braintree\CustomerSearch', 'Braintree_CustomerSearch');
class_alias('Braintree\DisbursementDetails', 'Braintree_DisbursementDetails');
class_alias('Braintree\Dispute', 'Braintree_Dispute');
class_alias('Braintree\Descriptor', 'Braintree_Descriptor');
class_alias('Braintree\Digest', 'Braintree_Digest');
class_alias('Braintree\Discount', 'Braintree_Discount');
class_alias('Braintree\DiscountGateway', 'Braintree_DiscountGateway');
class_alias('Braintree\IsNode', 'Braintree_IsNode');
class_alias('Braintree\EuropeBankAccount', 'Braintree_EuropeBankAccount');
class_alias('Braintree\EqualityNode', 'Braintree_EqualityNode');
class_alias('Braintree\Exception', 'Braintree_Exception');
class_alias('Braintree\Gateway', 'Braintree_Gateway');
class_alias('Braintree\Http', 'Braintree_Http');
class_alias('Braintree\KeyValueNode', 'Braintree_KeyValueNode');
class_alias('Braintree\Merchant', 'Braintree_Merchant');
class_alias('Braintree\MerchantGateway', 'Braintree_MerchantGateway');
class_alias('Braintree\MerchantAccount', 'Braintree_MerchantAccount');
class_alias('Braintree\MerchantAccountGateway', 'Braintree_MerchantAccountGateway');
class_alias('Braintree\MultipleValueNode', 'Braintree_MultipleValueNode');
class_alias('Braintree\MultipleValueOrTextNode', 'Braintree_MultipleValueOrTextNode');
class_alias('Braintree\OAuthGateway', 'Braintree_OAuthGateway');
class_alias('Braintree\PartialMatchNode', 'Braintree_PartialMatchNode');
class_alias('Braintree\Plan', 'Braintree_Plan');
class_alias('Braintree\PlanGateway', 'Braintree_PlanGateway');
class_alias('Braintree\RangeNode', 'Braintree_RangeNode');
class_alias('Braintree\ResourceCollection', 'Braintree_ResourceCollection');
class_alias('Braintree\RiskData', 'Braintree_RiskData');
class_alias('Braintree\ThreeDSecureInfo', 'Braintree_ThreeDSecureInfo');
class_alias('Braintree\SettlementBatchSummary', 'Braintree_SettlementBatchSummary');
class_alias('Braintree\SettlementBatchSummaryGateway', 'Braintree_SettlementBatchSummaryGateway');
class_alias('Braintree\SignatureService', 'Braintree_SignatureService');
class_alias('Braintree\Subscription', 'Braintree_Subscription');
class_alias('Braintree\SubscriptionGateway', 'Braintree_SubscriptionGateway');
class_alias('Braintree\SubscriptionSearch', 'Braintree_SubscriptionSearch');
class_alias('Braintree\TextNode', 'Braintree_TextNode');
class_alias('Braintree\Transaction', 'Braintree_Transaction');
class_alias('Braintree\TransactionGateway', 'Braintree_TransactionGateway');
class_alias('Braintree\Disbursement', 'Braintree_Disbursement');
class_alias('Braintree\TransactionSearch', 'Braintree_TransactionSearch');
class_alias('Braintree\TransparentRedirect', 'Braintree_TransparentRedirect');
class_alias('Braintree\TransparentRedirectGateway', 'Braintree_TransparentRedirectGateway');
class_alias('Braintree\Util', 'Braintree_Util');
class_alias('Braintree\Version', 'Braintree_Version');
class_alias('Braintree\Xml', 'Braintree_Xml');
class_alias('Braintree\WebhookNotification', 'Braintree_WebhookNotification');
class_alias('Braintree\WebhookTesting', 'Braintree_WebhookTesting');
class_alias('Braintree\CreditCardVerification', 'Braintree_CreditCardVerification');
class_alias('Braintree\CreditCardVerificationGateway', 'Braintree_CreditCardVerificationGateway');
class_alias('Braintree\CreditCardVerificationSearch', 'Braintree_CreditCardVerificationSearch');
class_alias('Braintree\PartnerMerchant', 'Braintree_PartnerMerchant');
class_alias('Braintree\PayPalAccount', 'Braintree_PayPalAccount');
class_alias('Braintree\PayPalAccountGateway', 'Braintree_PayPalAccountGateway');
class_alias('Braintree\PaymentMethod', 'Braintree_PaymentMethod');
class_alias('Braintree\PaymentMethodGateway', 'Braintree_PaymentMethodGateway');
class_alias('Braintree\PaymentMethodNonce', 'Braintree_PaymentMethodNonce');
class_alias('Braintree\PaymentMethodNonceGateway', 'Braintree_PaymentMethodNonceGateway');
class_alias('Braintree\PaymentInstrumentType', 'Braintree_PaymentInstrumentType');
class_alias('Braintree\UnknownPaymentMethod', 'Braintree_UnknownPaymentMethod');
class_alias('Braintree\TestingGateway', 'Braintree_TestingGateway');
