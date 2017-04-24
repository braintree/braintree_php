<?php
namespace Braintree;

/**
 * Braintree Gateway module
 *
 * @package    Braintree
 * @category   Resources
 */
class Gateway
{
    /**
     *
     * @var Configuration
     */
    public $config;

    public function __construct($config)
    {
        if (is_array($config)) {
            $config = new Configuration($config);
        }
        $this->config = $config;
    }

    /**
     *
     * @return AddOnGateway
     */
    public function addOn()
    {
        return new AddOnGateway($this);
    }

    /**
     *
     * @return AddressGateway
     */
    public function address()
    {
        return new AddressGateway($this);
    }

    /**
     *
     * @return ClientTokenGateway
     */
    public function clientToken()
    {
        return new ClientTokenGateway($this);
    }

    /**
     *
     * @return CreditCardGateway
     */
    public function creditCard()
    {
        return new CreditCardGateway($this);
    }

    /**
     *
     * @return CreditCardVerificationGateway
     */
    public function creditCardVerification()
    {
        return new CreditCardVerificationGateway($this);
    }

    /**
     *
     * @return CustomerGateway
     */
    public function customer()
    {
        return new CustomerGateway($this);
    }

    /**
     *
     * @return DiscountGateway
     */
    public function discount()
    {
        return new DiscountGateway($this);
    }

    /**
     *
     * @return MerchantGateway
     */
    public function merchant()
    {
        return new MerchantGateway($this);
    }

    /**
     *
     * @return MerchantAccountGateway
     */
    public function merchantAccount()
    {
        return new MerchantAccountGateway($this);
    }

    /**
     *
     * @return OAuthGateway
     */
    public function oauth()
    {
        return new OAuthGateway($this);
    }

    /**
     *
     * @return PaymentMethodGateway
     */
    public function paymentMethod()
    {
        return new PaymentMethodGateway($this);
    }

    /**
     *
     * @return PaymentMethodNonceGateway
     */
    public function paymentMethodNonce()
    {
        return new PaymentMethodNonceGateway($this);
    }

    /**
     *
     * @return PayPalAccountGateway
     */
    public function payPalAccount()
    {
        return new PayPalAccountGateway($this);
    }

    /**
     *
     * @return PlanGateway
     */
    public function plan()
    {
        return new PlanGateway($this);
    }

    /**
     *
     * @return SettlementBatchSummaryGateway
     */
    public function settlementBatchSummary()
    {
        return new SettlementBatchSummaryGateway($this);
    }

    /**
     *
     * @return SubscriptionGateway
     */
    public function subscription()
    {
        return new SubscriptionGateway($this);
    }

    /**
     *
     * @return TestingGateway
     */
    public function testing()
    {
        return new TestingGateway($this);
    }

    /**
     *
     * @return TransactionGateway
     */
    public function transaction()
    {
        return new TransactionGateway($this);
    }

    /**
     *
     * @return TransparentRedirectGateway
     */
    public function transparentRedirect()
    {
        return new TransparentRedirectGateway($this);
    }

    /**
     *
     * @return UsBankAccountGateway
     */
    public function usBankAccount()
    {
        return new UsBankAccountGateway($this);
    }

    /**
     *
     * @return IdealPaymentGateway
     */
    public function idealPayment()
    {
        return new IdealPaymentGateway($this);
    }

    /**
     *
     * @return WebhookNotificationGateway
     */
    public function webhookNotification()
    {
        return new WebhookNotificationGateway($this);
    }
}
class_alias('Braintree\Gateway', 'Braintree_Gateway');
