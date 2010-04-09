<?php

require_once 'PHPUnit/Framework.php';

set_include_path(
  get_include_path() . PATH_SEPARATOR .
  realpath(dirname(__FILE__)) . '/../lib'
);

require_once "Braintree.php";

Braintree_Configuration::environment('development');
Braintree_Configuration::merchantId('integration_merchant_id');
Braintree_Configuration::publicKey('integration_public_key');
Braintree_Configuration::privateKey('integration_private_key');

class Braintree_TestHelper
{
    public static function submitTrRequest($url, $regularParams, $trData)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HEADER, true);
        // curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array_merge($regularParams, array('tr_data' => $trData))));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        preg_match('/Location: .*\?(.*)/', $response, $match);
        return trim($match[1]);
    }

    public static function includesOnAnyPage($collection, $targetItem)
    {
        foreach ($collection->items() AS $item)
        {
            if ($item->id == $targetItem->id)
            {
                return true;
            }
        }

        if ($collection->isLastPage())
        {
            return false;
        }

        return Braintree_TestHelper::includesOnAnyPage($collection->nextPage(), $targetItem);
    }
}

?>
