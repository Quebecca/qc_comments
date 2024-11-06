<?php

namespace Qc\QcComments\Domain\Filter;

use Qc\QcComments\Configuration\TyposcriptConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class StatisticsFilter extends Filter
{
    protected const KEY_COMMENT_REASON= "commentReason";

    /**
     * @var string
     */
    protected string $commentReason = "%";

    protected TyposcriptConfiguration $typoscriptConfiguration;

    /**
     * @param string $lang
     * @param string $startDate
     * @param string $endDate
     * @param string $dateRange
     * @param int $depth
     * @param bool $includeEmptyPages
     * @param string $useful
     * @param string $commentReason
     */
    public function __construct(
        string $lang = '',
        string $startDate = '',
        string $endDate = '',
        string $dateRange ='1 day',
        int $depth = 1,
        bool $includeEmptyPages = false,
        string $useful = '%',
        string $commentReason = "%"
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
        $this->commentReason = $commentReason;
        $this->typoscriptConfiguration = GeneralUtility::makeInstance(TyposcriptConfiguration::class);
        $this->typoscriptConfiguration->setConfigurationType(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->typoscriptConfiguration->setSettings('tx_qccomments');
    }

    /**
     * This function is used to the options form typoscript and show them in the option field of the filter
     * @return array
     */
    public function getCommentsReasons(): array
    {
        $options = $this->typoscriptConfiguration->getNegativeCommentsReasonsForBE();
        $filterOptions = [];
        $filterOptions[''] = '--';
        foreach ($options as $key => $values) {
            $filterOptions[$values['code']] = $values['short_label'];
        }
        return $filterOptions;
    }

    /**
     * @return string
     */
    public function getCommentReason(): string
    {
        return $this->commentReason;
        //return str_replace("'", "", $this->commentReason);
    }

    /**
     * @param string $commentReason
     */
    public function setCommentReason(string $commentReason): void
    {
        $this->commentReason = $commentReason == '' ? '%' : $commentReason;
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                self::KEY_COMMENT_REASON => $this->getCommentReason() ?? "%"
            ]
        );
    }

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
            $values[parent::KEY_USEFUL],
            $values[self::KEY_COMMENT_REASON] ?? "%"

        );
    }

    /**
     * @return string
     */
     public function getUsibiltyCriteria(): string
     {
        return " useful not like 'NA'";
    }

    /**
     * @return string
     */
     public function getRecordVisibility() :string{
        return "and hidden_comment = 0";
    }

}
