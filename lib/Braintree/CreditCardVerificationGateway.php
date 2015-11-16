<?php
namespace Braintree;

class CreditCardVerificationGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Http($gateway->config);
    }

    public function fetch($query, $ids)
    {
        $criteria = [];
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }
        $criteria["ids"] = CreditCardVerificationSearch::ids()->in($ids)->toparam();
        $path = $this->_config->merchantPath() . '/verifications/advanced_search';
        $response = $this->_http->post($path, ['search' => $criteria]);

        return Util::extractattributeasarray(
            $response['creditCardVerifications'],
            'verification'
        );
    }

    public function search($query)
    {
        $criteria = [];
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }

        $path = $this->_config->merchantPath() . '/verifications/advanced_search_ids';
        $response = $this->_http->post($path, ['search' => $criteria]);
        $pager = [
            'object' => $this,
            'method' => 'fetch',
            'methodArgs' => [$query]
            ];

        return new ResourceCollection($response, $pager);
    }
}
class_alias('Braintree\CreditCardVerificationGateway', 'Braintree_CreditCardVerificationGateway');
