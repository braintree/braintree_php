<?php
class Braintree_WebhookTesting
{
    public static function sampleNotification($kind, $id)
    {
        $payload = base64_encode(self::sampleXml($kind, $id));

        return array(
            'signature' => 'signature',
            'payload' => $payload
        );
    }

    public static function sampleXml($kind, $id)
    {
        $subjectXml = self::subscriptionSampleXml($id);
        return "
        <notification>
            <timestamp type=\"datetime\"></timestamp>
            <kind>{$kind}</kind>
            <subject>{$subjectXml}</subject>
        </notification>
        ";
    }
    public static function subscriptionSampleXml($id)
    {
        $xml = "
        <subscription>
          <id>{$id}</id>
          <transactions type=\"array\">
          </transactions>
          <add_ons type=\"array\">
          </add_ons>
          <discounts type=\"array\">
          </discounts>
        </subscription>
        ";

        return $xml;
    }
}
