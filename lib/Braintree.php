<?php // phpcs:ignore

/**
 * Braintree PHP Library
 * Creates class_aliases for old class names replaced by PSR-4 Namespaces
 */

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'autoload.php');

if (version_compare(PHP_VERSION, '7.3.0', '<')) {
    throw new Braintree\Exception('PHP version >= 7.3.0 required');
}

// phpcs:ignore
class Braintree
{
    public static function requireDependencies()
    {
        $requiredExtensions = ['xmlwriter', 'openssl', 'dom', 'hash', 'curl'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                throw new Braintree\Exception('The Braintree library requires the ' . $ext . ' extension.');
            }
        }
    }
}

Braintree::requireDependencies();
