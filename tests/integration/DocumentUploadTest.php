<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class DocumentUploadTest extends Setup
{
    private $gateway;
    private $pngFile;

    public function __construct() {
        $this->gateway = new Braintree\Gateway([
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key'
        ]);

        $this->pngFile = fopen(dirname(__DIR__) . '/fixtures/bt_logo.png', 'rb');
    }

    public function testCreate_whenValid_returnsSuccessfulResult()
    {
        $result = Braintree\DocumentUpload::create([
            "kind" => Braintree\DocumentUpload::EVIDENCE_DOCUMENT,
            "file" => $this->pngFile
        ]);

        $this->assertTrue($result->success);
    }

    public function testCreate_withUnsupportedFileType_returnsError()
    {
    }

    public function testCreate_withMalformedFile_returnsError()
    {
    }

    public function testCreate_withInvalidKind_returnsError()
    {
    }

    public function testCreate_whenFileIsOver4Mb_returnsError()
    {
    }

    public function testCreate_whenInvalidSignature_throwsInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException', 'invalid keys: bad_key');

        Braintree\DocumentUpload::create([
            "kind" => Braintree\DocumentUpload::EVIDENCE_DOCUMENT,
            "bad_key" => "value"
        ]);
    }
}
