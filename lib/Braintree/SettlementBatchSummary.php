<?php
class Braintree_SettlementBatchSummary extends Braintree
{
    public static function generate($settlement_date, $groupByCustomField = NULL)
    {
        $criteria = array('settlement_date' => $settlement_date);
        if (isset($groupByCustomField))
        {
            $criteria['group_by_custom_field'] = $groupByCustomField;
        }
        $params = array('settlement_batch_summary' => $criteria);
        $response = Braintree_Http::post('/settlement_batch_summary', $params);
        return self::_verifyGatewayResponse($response);
    }

    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['settlementBatchSummary'])) {
            return new Braintree_Result_Successful(
                self::factory($response['settlementBatchSummary'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Braintree_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Braintree_Exception_Unexpected(
                "Expected settlementBatchSummary or apiErrorResponse"
            );
        }
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /**
     * @ignore
     */
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;
    }

    public function records()
    {
        return $this->_attributes['records'];
    }
}
