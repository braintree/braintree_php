<?php
/**
 * Digest encryption module
 *
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * Digest creates an HMAC-SHA1 hash for encrypting messages
 *
 * @copyright  2010 Braintree Payment Solutions
 */
class Braintree_Digest
{
    public static function hexDigest($string)
    {
        if(function_exists('hash_hmac')) {
            return self::_builtInHmacSha1($string, Braintree_Configuration::privateKey());
        } else {
            return self::_hmacSha1($string, Braintree_Configuration::privateKey());
        }
    }

    public static function secureCompare($left, $right)
    {
        if (strlen($left) != strlen($right)) {
            return false;
        }

        $leftBytes = unpack("C*", $left);
        $rightBytes = unpack("C*", $right);

        $result = 0;
        for ($i = 0; $i < strlen($left); $i++) {
            $result = $result | ($left[$i] ^ $right[$i]);
        }
        return $result == 0;
    }

    public static function _builtInHmacSha1($message, $key)
    {
        return hash_hmac('sha1', $message, sha1(Braintree_Configuration::privateKey(), true));
    }

    public static function _hmacSha1($message, $key)
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
