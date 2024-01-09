<?php

namespace BTStandard\Sniffs;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class PreferIsSetSniff implements Sniff
{
    public function register()
    {
        return array(T_STRING);
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if (str_contains($tokens[$stackPtr]['content'], "array_key_exists")) {
            $warning = 'isset is preferred over array_key_exists as it contains a null check. Ensure using array_key_exists is safe, otherwise use isset';
            $phpcsFile->addWarning($warning, $stackPtr, '');
        }
    }
}
?>