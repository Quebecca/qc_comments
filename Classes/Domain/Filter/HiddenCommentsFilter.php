<?php

namespace Qc\QcComments\Domain\Filter;

use Qc\QcComments\Configuration\TyposcriptConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class HiddenCommentsFilter extends Filter
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
        $this->typoscriptConfiguration = new TyposcriptConfiguration();
        $this->typoscriptConfiguration->setConfigurationType(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->typoscriptConfiguration->setSettings('tx_qccomments');
    }

    /**
     * This function is used to the options form typoscript and show them in the option field of the filter
     * @return array
     */
    public function getCommentsReasons(): array
    {
        $options = $this->typoscriptConfiguration->getReasonsForBE($this->useful);
        $filterOptions = [];
        $filterOptions[''] = '--';
        foreach ($options as $key => $values) {
            $filterOptions[$values['short_label']] = $values['short_label'];
        }
        return $filterOptions;
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
     * @return CommentsFilter
     */
    public static function getInstanceFromArray(array $values): HiddenCommentsFilter
    {
        return  new HiddenCommentsFilter(
            $values[parent::KEY_LANG],
            $values[parent::KEY_START_DATE],
            $values[parent::KEY_END_DATE],
            $values[parent::KEY_DATE_RANGE],
            $values[parent::KEY_DEPTH],
            $values[parent::KEY_INCLUDE_EMPTY_PAGES],
            $values[parent::KEY_USEFUL],
            $values[self::KEY_COMMENT_REASON] ?? "%",
        );
    }


    /**
     * @return string
     */
    public function getCommentReason(): string
    {
        return str_replace("'", "\\'", $this->commentReason);
    }

    /**
     * @param string $commentReason
     */
    public function setCommentReason(string $commentReason): void
    {
        $this->commentReason = $commentReason == '' ? '%' : $commentReason;
    }

    /**
     *
     * @param string $useful
     */
    public function setUseful(string $useful): void
    {
        //check if the useful filter changed to "Negative comment" we set default value for
        if($useful == '0' && $useful != $this->getUseful()){
            $this->setCommentReason('%');
        }
        $this->useful = $useful == '' ? '%' : $useful;
    }

    /**
     * @return string
     */
    public function getUsabilityCriteria(): string
    {
        $criteria =  " useful like '".$this->getUseful()."'and useful not like 'NA'";
        // we apply the reason only if the comment is negative
        if($this->getCommentReason() != "%" && ($this->getUseful() == '0' || $this->getUseful() == '1')){
            $criteria .= "AND reason_short_label like '".$this->getCommentReason()."'";
        }
        return $criteria;
    }

    /**
     * @return string
     */
    public function getRecordVisibility() :string {
        return ' and hidden_comment = 1';
    }
}
