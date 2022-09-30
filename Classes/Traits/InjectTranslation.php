<?php

namespace Qc\QcComments\Traits;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility as LocalizationUtilityExtbase;

trait InjectTranslation
{
    /**
     * @var string
     */
    protected $extKey;

    /**
     * This function is used to translate label by $key
     * @param $key
     * @param array $arguments
     * @param null $extKey
     * @return string
     */
    protected function translate($key, $arguments = [], $extKey = null): string
    {
        $extKey = $this->extKey;
        return $extKey != '' ? LocalizationUtilityExtbase::translate($key, $extKey, (array)$arguments) . $this::addTrKey($key) : '';
    }

    /**
     * This function is used to return the extension key
     * @param $key
     * @return string
     */
    protected static function addTrKey($key): string
    {
        if ($_GET['addTrKey'] == 1) {
            return " ($key)";
        }
        return '';
    }
}
