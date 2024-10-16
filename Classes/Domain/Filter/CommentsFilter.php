<?php

namespace Qc\QcComments\Domain\Filter;

class CommentsFilter extends Filter
{
    protected const KEY_INCLUDE_DELETED_COMMENTS= false;

    public bool $includeDeletedComments = false;


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
        bool $includeDeletedComments = false
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
        $this->includeDeletedComments = $includeDeletedComments;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [self::KEY_INCLUDE_DELETED_COMMENTS => $this->getIncludeDeletedComments() ?? false]
        );
    }

    /**
     * This function is used to map array to filter object
     * @param array $values
     * @return CommentsFilter
     */
    public static function getInstanceFromArray(array $values): CommentsFilter
    {
        return  new CommentsFilter(
            $values[parent::KEY_LANG],
            $values[parent::KEY_START_DATE],
            $values[parent::KEY_END_DATE],
            $values[parent::KEY_DATE_RANGE],
            $values[parent::KEY_DEPTH],
            $values[parent::KEY_INCLUDE_EMPTY_PAGES],
            $values[parent::KEY_USEFUL],
            $values[self::KEY_INCLUDE_DELETED_COMMENTS] ?? false
        );
    }


    /**
     * @return bool
     */
    public function getIncludeDeletedComments(): bool
    {
        return $this->includeDeletedComments;
    }

    /**
     * @param bool $includeDeletedComments
     */
    public function setIncludeDeletedComments(bool $includeDeletedComments): void
    {
        $this->includeDeletedComments = $includeDeletedComments;
    }

    public function getConstraints() : string{

        $criteria = $this->getLangCriteria()
            .$this->getDateCriteria();



        return $criteria;
    }

    /**
     * @return string
     */
    public function getUsibiltyCriteria(): string
    {
        return " useful like '".$this->getUseful()."'";
    }

    /**
     * @return bool
     */
    public function getRecordVisbility() :bool {
        return $this->getIncludeDeletedComments();
    }
}
