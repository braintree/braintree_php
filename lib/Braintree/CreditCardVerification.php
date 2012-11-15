<?php
class Braintree_CreditCardVerification extends Braintree_Result_CreditCardVerification
{
    public static function factory($attributes)
    {
        $instance = new self($attributes);
        return $instance;
    }

    public static function fetch($query, $ids)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }
        $criteria["ids"] = Braintree_CreditCardVerificationSearch::ids()->in($ids)->toparam();
        $response = braintree_http::post('/verifications/advanced_search', array('search' => $criteria));

        return braintree_util::extractattributeasarray(
            $response['creditCardVerifications'],
            'verification'
        );
    }

    public static function search($query)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }

        $response = braintree_http::post('/verifications/advanced_search_ids', array('search' => $criteria));
        $pager = array(
            'className' => __CLASS__,
            'classMethod' => 'fetch',
            'methodArgs' => array($query)
            );

        return new Braintree_ResourceCollection($response, $pager);
    }
}
