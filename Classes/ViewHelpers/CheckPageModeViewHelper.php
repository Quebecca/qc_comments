<?php

namespace Qc\QcComments\ViewHelpers;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CheckPageModeViewHelper extends AbstractViewHelper
{

    public function initializeArguments()
    {
        $this->registerArgument('pageUid', 'int', 'Page uid', true);
    }

    /**
     * @return bool
     */
    public function render(): bool
    {
        return $this->getBasPageMode($this->arguments['pageUid']);
    }

    /**
     * This function is used to check bas page mode for given page
     * @param int $pageUid
     * @return bool
     */
    public function getBasPageMode(int $pageUid): bool
    {
        /*
           ['Non précisé', 'not specified', ''],
                    ['Afficher pour cette page et ses sous-pages', 'mode 1', ''],
                    ['Afficher pour cette page seulement', 'mode 2', ''],
                    ['Masquer pour cette page et ses sous-pages', 'mode 3', ''],
                    ['Masquer pour cette page seulement', 'mode 4', ''],
        */
        $data = BackendUtility::getRecord('pages', $pageUid, 'uid,pid,tx_select_bas_page_mode');
        $enabledMode = ['mode 1', 'mode 2'];
        $disabledMode = ['mode 3', 'mode 4'];
        $inheritanceMode = ['', 'not specified'];
        $currentMode = $data['tx_select_bas_page_mode'];

        if (in_array($currentMode, $enabledMode) || in_array($currentMode, $disabledMode)) {
            return in_array($currentMode, $enabledMode);
        }

        // check parentes page
        $pageUid = $data['pid'];
        while ($pageUid != 0 && in_array($currentMode, $inheritanceMode)) {
            $data = BackendUtility::getRecord('pages', $pageUid, 'uid,pid,tx_select_bas_page_mode');
            $currentMode = $data['tx_select_bas_page_mode'];
            $pageUid = $data['pid'];
        }
        // if the parent page has the mode 1
        return $currentMode == 'mode 1';
    }
}
