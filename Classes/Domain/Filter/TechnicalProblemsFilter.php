<?php

namespace Qc\QcComments\Domain\Filter;


class TechnicalProblemsFilter extends Filter
{
    protected const KEY_INCLUDE_FIXED_TECHNICAL_PROBLEMS = 'includeFixedTechnicalProblems';

    /**
     * @var bool
     */
    public bool $includeFixedTechnicalProblems = false;


    /**
     * @param string $lang
     * @param string $startDate
     * @param string $endDate
     * @param string $dateRange
     * @param int $depth
     * @param bool $includeEmptyPages
     * @param string $useful
     * @param bool $includeFixedTechnicalProblems
     */
    public function __construct(
        string $lang = '',
        string $startDate = '',
        string $endDate = '',
        string $dateRange ='1 day',
        int $depth = 1,
        bool $includeEmptyPages = false,
        bool $includeFixedTechnicalProblems = false
    ) {
        parent::__construct(
            $lang,
            $startDate,
            $endDate,
            $dateRange,
            $depth,
            $includeEmptyPages,
            "NA"
        );
        $this->includeFixedTechnicalProblems = $includeFixedTechnicalProblems;
    }


    /**
     * @param bool $includeFixedTechnicalProblems
     */
    public function setIncludeFixedTechnicalProblems(bool $includeFixedTechnicalProblems): void
    {
        $this->includeFixedTechnicalProblems = $includeFixedTechnicalProblems;
    }

    /**
     * @return bool
     */
    public function getIncludeFixedTechnicalProblems(): bool
    {
        return $this->includeFixedTechnicalProblems;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                self::KEY_INCLUDE_FIXED_TECHNICAL_PROBLEMS => $this->getIncludeFixedTechnicalProblems() ?? false,
            ],
        );
    }

    /**
     * This function is used to map array to filter object
     * @param array $values
     * @return TechnicalProblemsFilter
     */
    public static function getInstanceFromArray(array $values) : TechnicalProblemsFilter
    {
         return new TechnicalProblemsFilter(
              $values[parent::KEY_LANG],
              $values[parent::KEY_START_DATE],
              $values[parent::KEY_END_DATE],
              $values[parent::KEY_DATE_RANGE],
              $values[parent::KEY_DEPTH],
              $values[parent::KEY_INCLUDE_EMPTY_PAGES],
              $values[self::KEY_INCLUDE_FIXED_TECHNICAL_PROBLEMS] ?? false
          );
    }
    /**
     * @return string
     */
    public function getUsibiltyCriteria() :string {
        return " useful like 'NA'";
    }

    /**
     * @return bool
     */
    public function getRecordVisibility() :bool{
        return $this->getIncludeFixedTechnicalProblems();
    }
}
