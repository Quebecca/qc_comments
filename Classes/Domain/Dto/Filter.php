<?php

namespace Qc\QcComments\Domain\Dto;
use Qc\QcComments\Traits\InjectTranslation;

class Filter
{
    use InjectTranslation;

    function __construct()
    {
        $this->extKey = 'qc_comments';
    }

    /**
     * @var string
     */
    protected $lang = 'fr';

    /**
     * @var string
     */
    public $startDate;

    /**
     * @var string
     */
    public $endDate;

    /**
     * @var bool
     */
    protected $includeEmptyPages = true;

    /**
     * @var int
     */
    protected $depth = 0;

    /**
     * @return bool
     */
    public function getIncludeEmptyPages(): bool
    {
        return $this->includeEmptyPages;
    }

    /**
     * @param bool $includeEmptyPages
     *
     * @return Filter
     */
    public function setIncludeEmptyPages(bool $includeEmptyPages): Filter
    {
        $this->includeEmptyPages = $includeEmptyPages;
        return $this;
    }

    /**
     * @var string
     */
    protected $dateRange = '1 day';

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     */
    public function setDepth(int $depth): Filter
    {
        $this->depth = $depth;
        return $this;
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     * @return Filter
     */
    public function setLang(string $lang): Filter
    {
        $this->lang = $lang;
        return $this;
    }

    public function getLangCriteria()
    {
        $criteria = '';
        switch ($this->lang) {
            case 'fr':
                $criteria = "and url_orig not like '%/en/%'";
                break;
            case 'en':
                $criteria = "and url_orig like '%/en/%'";
                break;
        }
        return $criteria;
    }

    /**
     * @return string
     */
    public function getDateRange(): string
    {
        return $this->dateRange;
    }

    public function getDateForRange($format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime('-' . $this->getDateRange()));
    }

    /**
     * @param string $date_range
     */
    public function setDateRange(string $date_range): Filter
    {
        $this->dateRange = $date_range;
        return $this;
    }

    public function getDepthOptions()
    {
        return [
            0 => $this->translate('filter.depth.thisPage'),
            1 => '1 ' . $this->translate('filter.depth.level'),
            2 => '2 ' . $this->translate('filter.depth.levels'),
            3 => '3 ' . $this->translate('filter.depth.levels'),
            999 => $this->translate('filter.depth.limitless'),
        ];
    }

    public function getDateRangeOptions()
    {
        return [
            '1 day' => '1 ' . $this->translate('filter.dateRange.day'),
            '2 day' => '2 ' . $this->translate('filter.dateRange.days'),
            '1 week' => '1 ' . $this->translate('filter.dateRange.week'),
            '2 week' => '2 ' . $this->translate('filter.dateRange.weeks'),
            '1 month' => '1 ' . $this->translate('filter.dateRange.month'),
            '3 month' => '3 ' . $this->translate('filter.dateRange.months'),
            '6 month' => '6 ' . $this->translate('filter.dateRange.months'),
            '1 year' => '1 ' . $this->translate('filter.dateRange.year'),
            'userDefined' =>  $this->translate('userDefined')
        ];
    }

    public function getLangOptions()
    {
        return [
            '' => $this->translate('filter.lang.all'),
            'fr' => $this->translate('filter.lang.french'),
            'en' => $this->translate('filter.lang.english'),
        ];
    }

    public function setStartDate(string $startDate = null) {
        $this->startDate = $startDate;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setEndDate(string $endDate = null) {
        $this->endDate = $endDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getDateCriteria()
    {
        $criteria = '';

        if($this->getDateRange() === 'userDefined'){
            if($this->startDate != ''){
                // delete minutes seconds
                $formatedStartDate = explode(' ', $this->startDate);
                $criteria = " and date_houre >= '". date('Y-m-d H:i:s', strtotime($formatedStartDate[0]))."'";
            }
            if($this->endDate != ''){
                $formatedEndDate = explode(' ', $this->endDate);
                $criteria .= " and date_houre <= '". date('Y-m-d H:i:s',strtotime($formatedEndDate[0]))."'";
            }
        }
       else{
           $criteria = " and date_houre >= '" . $this->getDateForRange()."'";
       }
        return $criteria;
    }
}
