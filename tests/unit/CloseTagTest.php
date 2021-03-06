<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;

class CloseTagTest extends Setup
{
    public function testCodeFiles_allOmitPHPCloseTag()
    {
        $codeFiles = explode("\n", shell_exec("find ./lib -name \*.php"));
        foreach ($codeFiles as $codeFile) {
            if ($codeFile == "") {
                continue;
            }
            $code = file_get_contents($codeFile);
            $this->assertStringNotContainsString("?>", $code, "$codeFile should not contain a PHP close tag");
        }
    }
}
