<?php
class Braintree_CreditCardVerificationGateway
{
    private $_gateway;
    private $_config;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
    }

    public function fetch($query, $ids)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }
        $criteria["ids"] = Braintree_CreditCardVerificationSearch::ids()->in($ids)->toparam();
        $response = $this->_config->http()->post('/verifications/advanced_search', array('search' => $criteria));

        return Braintree_Util::extractattributeasarray(
            $response['creditCardVerifications'],
            'verification'
        );
    }

    public function search($query)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }

        $response = $this->_config->http()->post('/verifications/advanced_search_ids', array('search' => $criteria));
        $pager = array(
            'object' => $this,
            'method' => 'fetch',
            'methodArgs' => array($query)
            );

        return new Braintree_ResourceCollection($response, $pager);
    }
}
