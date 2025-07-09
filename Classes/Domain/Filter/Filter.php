<?php

namespace Qc\QcComments\Domain\Filter;

/***
 *
 * This file is part of Qc Comments project.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 <techno@quebec.ca>
 *
 ***/

use DateTime;
use DateTimeZone;
use Qc\QcComments\Util\Arrayable;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

abstract class Filter implements Arrayable
{
    protected const KEY_LANG = 'lang';
    protected const KEY_START_DATE = 'startDate';
    protected const KEY_END_DATE = 'endDate';
    protected const KEY_DATE_RANGE = 'dateRange';
    protected const KEY_DEPTH = 'depth';
    protected const KEY_INCLUDE_EMPTY_PAGES = 'includeEmptyPages';

    protected const KEY_USEFUL = 'useful';

    protected string $tableName = "tx_qccomments_domain_model_comment";


    /**
     * @var string
     */
    public string $useful = '';
    /**
     * @var bool
     */
    public bool $includeEmptyPages = true;

    /**
     * @var LocalizationUtility
     */
    protected LocalizationUtility $localizationUtility;

    /**
     * @var string
     */
    public string $lang = 'fr';

    /**
     * @var string
     */
    public string $startDate = '';

    /**
     * @var string
     */
    public string $endDate = '';


    /**
     * @var int
     */
    public int $depth = 0;

    /**
     * @var string
     */
    public string $extKey;



    const QC_LANG_FILE = 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:';

    public function __construct(
        string $lang = '',
        string $startDate = '',
        string $endDate = '',
        string $dateRange ='1 day',
        int $depth = 1,
        bool $includeEmptyPages = false,
        string $useful = ""
    ) {
        $this->lang = $lang;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->depth = $depth;
        $this->dateRange = $dateRange;
        $this->extKey = 'qc_comments';
        $this->includeEmptyPages = $includeEmptyPages;
        $this->useful = $useful;
        $this->localizationUtility
            = GeneralUtility::makeInstance(LocalizationUtility::class);
    }

    /**
     * @var string
     */
    public string $dateRange = '1 day';

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     * @return Filter
     */
    public function setDepth(int $depth): Filter
    {
        $this->depth = $depth;
        return $this;
    }

    /**
     * @return string
     */
    public function getUseful(): string
    {
        return $this->useful;
    }

    /**
     * @param string $useful
     */
    public function setUseful(string $useful): void
    {
        $this->useful = $useful == '' ? '%' : $useful;
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

    public function getLangCriteria(): string
    {
        $criteria = '';
        switch ($this->lang) {
            case 'fr':
                $criteria = "and ".$this->tableName.".sys_language_uid  = 0";
                break;
            case 'en':
                $criteria = "and ".$this->tableName.".sys_language_uid  = 1";
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

    public function getDateForRange($format = 'Y-m-d H:i:s'): string
    {
        return date($format, strtotime('-' . $this->getDateRange()));
    }

    /**
     * @param string $date_range
     * @return Filter
     */
    public function setDateRange(string $date_range): Filter
    {
        $this->dateRange = $date_range;
        return $this;
    }

    /**
     * @return array
     */
    public function getDepthOptions(): array
    {
        return [
            0 => $this->localizationUtility
                ->translate(self::QC_LANG_FILE . 'filter.depth.thisPage'),
            1 => '1 ' . $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'filter.depth.level'),
            2 => '2 ' . $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'filter.depth.levels'),
            3 => '3 ' . $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'filter.depth.levels'),
            999 => $this->localizationUtility
                ->translate(self::QC_LANG_FILE . 'filter.depth.limitless'),
        ];
    }

    /**
     * @return array
     */
    public function getDateRangeOptions(): array
    {
        return [
            '1 day' => '1 ' . $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'filter.dateRange.day'),
            '2 day' => '2 ' . $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'filter.dateRange.days'),
            '1 week' => '1 ' . $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'filter.dateRange.week'),
            '2 week' => '2 ' . $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'filter.dateRange.weeks'),
            '1 month' => '1 ' . $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'filter.dateRange.month'),
            '3 month' => '3 ' . $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'filter.dateRange.months'),
            '6 month' => '6 ' . $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'filter.dateRange.months'),
            '1 year' => '1 ' . $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'filter.dateRange.year'),
            'userDefined' =>  $this->localizationUtility
                ->translate(self::QC_LANG_FILE . 'userDefined')
        ];
    }

    /**
     * @return array
     */
    public function getLangOptions(): array
    {
        return [
            '' => $this->localizationUtility
                ->translate(self::QC_LANG_FILE . 'filter.lang.all'),
            'fr' => $this->localizationUtility
                ->translate(self::QC_LANG_FILE . 'filter.lang.french'),
            'en' => $this->localizationUtility
                ->translate(self::QC_LANG_FILE . 'filter.lang.english'),
        ];
    }

    /**
     * @return array
     */
    public function getCommentsUtility(): array
    {
        return [
            '' => $this->localizationUtility
                ->translate(self::QC_LANG_FILE . 'all'),
            '0' => $this->localizationUtility
                ->translate(self::QC_LANG_FILE . 'notUseful'),
            '1' => $this->localizationUtility
                ->translate(self::QC_LANG_FILE . 'comments.h.useful'),
        ];
    }

    /**
     * @param string|null $startDate
     */
    public function setStartDate(string $startDate = null): void
    {
        $this->startDate = $startDate ?? '';
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getEndDate(): string
    {
        if($this->endDate != ''){
            $date = new DateTime($this->endDate, new DateTimeZone('UTC'));
            return $date->format('Y-m-d');
        }
        return $this->endDate;

    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getStartDate(): string
    {
        if($this->startDate != ''){
            $date = new DateTime($this->startDate, new DateTimeZone('UTC'));
            return $date->format('Y-m-d');
        }
        return $this->startDate;

    }

    /**
     * @param string|null $endDate
     */
    public function setEndDate(string $endDate = null): void
    {
        $this->endDate = $endDate ?? '';
    }


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
     * This function is used to return date criteria
     * @return string
     */
    public function getDateCriteria(): string
    {
        $criteria = '';

        if ($this->getDateRange() === 'userDefined') {
            if ($this->startDate != '') {
                // delete minutes seconds
                $formatedStartDate = explode(' ', $this->startDate);
                $criteria = " and Date(date_hour) >= '"
                    . date('Y-m-d H:i:s', strtotime($formatedStartDate[0])) . "'";
            }
            if ($this->endDate != '') {
                $formatedEndDate = explode(' ', $this->endDate);
                $criteria .= " and Date(date_hour) <= '"
                    . date('Y-m-d H:i:s', strtotime($formatedEndDate[0])) . "'";
            }
        } else {
            $criteria = " and Date(date_hour) >= '" . $this->getDateForRange() . "'";
        }
        return $criteria;
    }


    /**
     * This function is used to check for the useful field filter
     * @return string
     */
    abstract public function getUsabilityCriteria():string;

    /**
     * This function is used to check if we display the deleted record or not
     * @return bool
     */
    abstract public function getRecordVisibility():string;


    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
          self::KEY_LANG => $this->getLang() ?? '',
          self::KEY_START_DATE => $this->getStartDate() ?? '',
          self::KEY_END_DATE => $this->getEndDate()  ?? '',
          self::KEY_DATE_RANGE => $this->getDateRange()  ?? '',
          self::KEY_DEPTH => $this->getDepth()  ?? '',
          self::KEY_INCLUDE_EMPTY_PAGES => $this->getIncludeEmptyPages() ?? false,
          self::KEY_USEFUL => $this->getUseful() ?? '',
        ];
    }

    /**
     * This function is used to map array to filter object
     * @param array $values
     * @return Filter
     */
     public static function getInstanceFromArray(array $values){}
}
