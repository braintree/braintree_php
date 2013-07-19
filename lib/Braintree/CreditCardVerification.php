<?php

namespace Braintree;

class CreditCardVerification extends Result\CreditCardVerification
{
    public static function factory($attributes)
    {
        $instance = new self($attributes);
        return $instance;
    }

    /**
     * @param IsNode[] $query
     * @param Int[] $ids
     * @return object[]
     */
    public static function fetch($query, $ids)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }
        $criteria["ids"] = CreditCardVerificationSearch::ids()->in($ids)->toparam();
        $response = Http::post('/verifications/advanced_search', array('search' => $criteria));

        return Util::extractattributeasarray(
            $response['creditCardVerifications'],
            'verification'
        );
    }

    /**
     * @param IsNode[] $query
     * @return ResourceCollection
     */
    public static function search($query)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }

        $response = Http::post('/verifications/advanced_search_ids', array('search' => $criteria));
        $pager = array(
            'className' => __CLASS__,
            'classMethod' => 'fetch',
            'methodArgs' => array($query)
            );

        return new ResourceCollection($response, $pager);
    }
}
