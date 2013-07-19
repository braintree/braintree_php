<?php

namespace Braintree;

class SettlementBatchSummary extends Braintree
{
    public static function generate($settlement_date, $groupByCustomField = NULL)
    {
        $criteria = array('settlement_date' => $settlement_date);
        if (isset($groupByCustomField))
        {
            $criteria['group_by_custom_field'] = $groupByCustomField;
        }
        $params = array('settlement_batch_summary' => $criteria);
        $response = Http::post('/settlement_batch_summary', $params);

        if (isset($groupByCustomField))
        {
            $response['settlementBatchSummary']['records'] = self::_underscoreCustomField(
                $groupByCustomField,
                $response['settlementBatchSummary']['records']
            );
        }

        return self::_verifyGatewayResponse($response);
    }

    private static function _underscoreCustomField($groupByCustomField, $records)
    {
        $updatedRecords = array();

        foreach ($records as $record)
        {
            $camelized = Util::delimiterToCamelCase($groupByCustomField);
            $record[$groupByCustomField] = $record[$camelized];
            unset($record[$camelized]);
            $updatedRecords[] = $record;
        }

        return $updatedRecords;
    }

    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['settlementBatchSummary'])) {
            return new Result\Successful(
                self::factory($response['settlementBatchSummary'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Result\Error($response['apiErrorResponse']);
        } else {
            throw new Exception\Unexpected(
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
