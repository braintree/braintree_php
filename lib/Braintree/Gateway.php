<?php // phpcs:disable Generic.Commenting.DocComment.MissingShort

namespace Braintree;

use Braintree\HttpHelpers\HttpClient;

/**
 * Braintree Gateway module
 */
class Gateway
{
    /**
     *
     * @var Configuration
     */
    public $config;

    /**
     *
     * @var GraphQLClient
     */
    public $graphQLClient;

    protected HttpClient $http;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($config, HttpClient $http = null)
    {
        if (is_array($config)) {
            $config = new Configuration($config);
        }
        $this->config = $config;
        $this->http = $http ? $http->setConfig($config) : new Http($config);
        $this->graphQLClient = new GraphQLClient($config);
    }

    /**
     *
     * @return AddOnGateway
     */
    public function addOn()
    {
        return (new AddOnGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return AddressGateway
     */
    public function address()
    {
        return (new AddressGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return ApplePayGateway
     */
    public function applePay()
    {
        return (new ApplePayGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return ClientTokenGateway
     */
    public function clientToken()
    {
        return (new ClientTokenGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return CreditCardGateway
     */
    public function creditCard()
    {
        return (new CreditCardGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return CreditCardVerificationGateway
     */
    public function creditCardVerification()
    {
        return (new CreditCardVerificationGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return CustomerGateway
     */
    public function customer()
    {
        return (new CustomerGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return DiscountGateway
     */
    public function discount()
    {
        return (new DiscountGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return DisputeGateway
     */
    public function dispute()
    {
        return (new DisputeGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return DocumentUploadGateway
     */
    public function documentUpload()
    {
        return (new DocumentUploadGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return ExchangeRateQuoteGateway
     */
    public function exchangeRateQuote()
    {
        return (new ExchangeRateQuoteGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return MerchantGateway
     */
    public function merchant()
    {
        return (new MerchantGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return MerchantAccountGateway
     */
    public function merchantAccount()
    {
        return (new MerchantAccountGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return OAuthGateway
     */
    public function oauth()
    {
        return (new OAuthGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return PaymentMethodGateway
     */
    public function paymentMethod()
    {
        return (new PaymentMethodGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return PaymentMethodNonceGateway
     */
    public function paymentMethodNonce()
    {
        return (new PaymentMethodNonceGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return PayPalAccountGateway
     */
    public function payPalAccount()
    {
        return (new PayPalAccountGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return SepaDirectDebitAccountGateway
     */
    public function sepaDirectDebitAccount()
    {
        return (new SepaDirectDebitAccountGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return PlanGateway
     */
    public function plan()
    {
        return (new PlanGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return SettlementBatchSummaryGateway
     */
    public function settlementBatchSummary()
    {
        return (new SettlementBatchSummaryGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return SubscriptionGateway
     */
    public function subscription()
    {
        return (new SubscriptionGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return TestingGateway
     */
    public function testing()
    {
        return (new TestingGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return TransactionGateway
     */
    public function transaction()
    {
        return (new TransactionGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return TransactionLineItemGateway
     */
    public function transactionLineItem()
    {
        return (new TransactionLineItemGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return UsBankAccountGateway
     */
    public function usBankAccount()
    {
        return (new UsBankAccountGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return UsBankAccountVerificationGateway
     */
    public function usBankAccountVerification()
    {
        return (new UsBankAccountVerificationGateway($this))->setHttpClient($this->http);
    }

    /**
     *
     * @return WebhookNotificationGateway
     */
    public function webhookNotification()
    {
        return new WebhookNotificationGateway($this);
    }

    /**
     *
     * @return WebhookTestingGateway
     */
    public function webhookTesting()
    {
        return new WebhookTestingGateway($this);
    }
}
