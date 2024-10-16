<?php

namespace Qc\QcComments\Domain\Filter;


class TechnicalProblemsFilter extends Filter
{
    protected const KEY_INCLUDE_FIXED_TECHNICAL_PROBLEM = 'includeFixedTechnicalProblem';

    /**
     * @var bool
     */
    public bool $includeFixedTechnicalProblem = false;


    /**
     * @param string $lang
     * @param string $startDate
     * @param string $endDate
     * @param string $dateRange
     * @param bool $includeEmptyPages
     * @param int $depth
     * @param string $useful
     */
    public function __construct(
        string $lang = '',
        string $startDate = '',
        string $endDate = '',
        string $dateRange ='1 day',
        int $depth = 1,
        bool $includeEmptyPages = false,
        string $useful = '',
    ) {
        parent::__construct(
            $lang,
            $startDate,
            $endDate,
            $dateRange,
            $depth,
            $includeEmptyPages,
            $useful
        );
    }


    /**
     * @return bool
     */
    public function getIncludeFixedTechnicalProblem(): bool
    {
        return $this->includeFixedTechnicalProblem;
    }

    /**
     * @param bool $includeFixedTechnicalProblem
     */
    public function setIncludeFixedTechnicalProblem(bool $includeFixedTechnicalProblem): void
    {
        $this->includeFixedTechnicalProblem = $includeFixedTechnicalProblem;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
/*        return array_merge(
            parent::toArray(),
            [
                self::KEY_INCLUDE_FIXED_TECHNICAL_PROBLEM => $this->getIncludeFixedTechnicalProblem() ?? false,
                self::KEY_INCLUDE_EMPTY_PAGES => $this->getIncludeEmptyPages() ?? false
            ],
        );*/
        return parent::toArray();
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
              $values[self::KEY_INCLUDE_FIXED_TECHNICAL_PROBLEM] ?? false
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
    public function getRecordVisbility() :bool{
        return $this->getIncludeFixedTechnicalProblem();
    }
}
