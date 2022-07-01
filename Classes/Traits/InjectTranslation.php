<?php

namespace Qc\QcComments\Traits;

use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility as LocalizationUtilityExtbase;

trait InjectTranslation
{
    use injectT3Utilities;
    /**
     * @var string
     */
    protected $extKey;

    /**
     * @param $key
     * @param array $arguments
     * @param null $extKey
     * @return string
     */
    protected function translate($key,  $arguments = [], $extKey = null)
    {
        $extKey = $extKey
                    ?? $this->extKey
                    ?? ($this instanceof ActionController ? $this->request->getControllerExtensionKey() : false);

        return $extKey != '' ? LocalizationUtilityExtbase::translate($key, $extKey, (array) $arguments).self::addTrKey($key) : '';
    }

}