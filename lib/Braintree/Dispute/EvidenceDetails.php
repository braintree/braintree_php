<?php
namespace Braintree\Dispute;

use Braintree\Instance;

/**
 * Evidence details for a dispute
 *
 * @package    Braintree
 *
 * @property-read string $comment
 * @property-read date   $created_at
 * @property-read string $id
 * @property-read string $sent_to_processor_at
 * @property-read string $url
 */
class EvidenceDetails extends Instance
{
}

class_alias('Braintree\Dispute\EvidenceDetails', 'Braintree_Dispute_EvidenceDetails');
