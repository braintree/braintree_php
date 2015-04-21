<?php

namespace Test\Unitests;

require_once dirname(__DIR__).'/Setup.php';

use Test\Setup;

class SanityTest extends Setup
{
    public function testCodeFiles_allOmitPHPCloseTag()
    {
        $codeFiles = explode("\n", shell_exec("find ./src -name \*.php"));
        foreach ($codeFiles as $codeFile) {
            if ($codeFile == '') {
                continue;
            }
            $code = file_get_contents($codeFile);
            $this->assertNotContains('?>', $code, "$codeFile should not contain a PHP close tag");
        }
    }
}
