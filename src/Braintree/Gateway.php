<?php
namespace Braintree;

/**
 * Braintree Gateway module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Gateway
{
    public $config;

    public function __construct($config)
    {
        if (is_array($config)) {
            $config = new Configuration($config);
        }

        $this->config = $config;
    }

    public function addOn()
    {
        return new AddOnGateway($this);
    }

    public function address()
    {
        return new AddressGateway($this);
    }

    public function clientToken()
    {
        return new ClientTokenGateway($this);
    }

    public function creditCard()
    {
        return new CreditCardGateway($this);
    }

    public function creditCardVerification()
    {
        return new CreditCardVerificationGateway($this);
    }

    public function customer()
    {
        return new CustomerGateway($this);
    }

    public function discount()
    {
        return new DiscountGateway($this);
    }

    public function merchant()
    {
        return new MerchantGateway($this);
    }

    public function merchantAccount()
    {
        return new MerchantAccountGateway($this);
    }

    public function oauth()
    {
        return new OAuthGateway($this);
    }

    public function paymentMethod()
    {
        return new PaymentMethodGateway($this);
    }

    public function paymentMethodNonce()
    {
        return new PaymentMethodNonceGateway($this);
    }

    public function payPalAccount()
    {
        return new PayPalAccountGateway($this);
    }

    public function plan()
    {
        return new PlanGateway($this);
    }

    public function settlementBatchSummary()
    {
        return new SettlementBatchSummaryGateway($this);
    }

    public function subscription()
    {
        return new SubscriptionGateway($this);
    }

    public function transaction()
    {
        return new TransactionGateway($this);
    }

    public function transparentRedirect()
    {
        return new TransparentRedirectGateway($this);
    }
}