<?php
/**
 * Digest encryption module
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Digest creates an HMAC-SHA1 hash for encrypting messages
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2010 Braintree Payment Solutions
 */
class Braintree_Digest
{
    /**
     * public interface
     * @access public
     * @param var $string
     * @return var HMAC-SHA1 hash of passed string
     */
    public static function hexDigest($string)
    {
        if(function_exists('hash_hmac')) {
            return hash_hmac('sha1',
                             $string,
                             sha1(Braintree_Configuration::privateKey(), true)
                            );
        } else {
            return self::_hmacSHA1($string, 
                                   Braintree_Configuration::privateKey()
                                  );
        }
    }

    /**
     * based on the PHP hash_hmac() function & Braintree_Ruby _hmac_sha1
     * in case hash_hmac isn't available
     *
     * @access protected
     * @param var $message message to be embedded in the hash
     * @param var $key private hash key
     * @return var hexadecimal HMAC-SHA1 hash
     */
    private function _hmacSHA1($message, $key)
    {
        $pack = 'H40';
        $keyDigest = sha1($key,true);
        $innerPad = str_repeat(chr(0x36), 64);
        $outerPad = str_repeat(chr(0x5C), 64);

        for ($i = 0; $i < 20; $i++) {
            $innerPad{$i} = $keyDigest{$i} ^ $innerPad{$i};
            $outerPad{$i} = $keyDigest{$i} ^ $outerPad{$i};
        }

        return sha1($outerPad.pack($pack, sha1($innerPad.$message)));
    }
}
