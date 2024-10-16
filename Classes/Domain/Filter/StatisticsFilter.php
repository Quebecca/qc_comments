<?php

namespace Qc\QcComments\Domain\Filter;

class StatisticsFilter extends Filter
{

    /**
     * This function is used to map array to filter object
     * @param array $values
     * @return StatisticsFilter
     */
    public static function getInstanceFromArray(array $values): StatisticsFilter
    {
        return  new StatisticsFilter(
            $values[parent::KEY_LANG],
            $values[parent::KEY_START_DATE],
            $values[parent::KEY_END_DATE],
            $values[parent::KEY_DATE_RANGE],
            $values[parent::KEY_DEPTH],
            $values[parent::KEY_INCLUDE_EMPTY_PAGES],
            $values[parent::KEY_USEFUL]
        );
    }

    /**
     * @return string
     */
     public function getUsibiltyCriteria(): string
     {
        return "true";
    }

    /**
     * @return bool
     */
     public function getRecordVisbility() :bool{
        return false;
    }

}
