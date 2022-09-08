<?php
namespace Qc\QcComments\ViewHelpers;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CheckBasPageModeViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('pageUid', 'int', 'Page uid', true);

    }

    /**
     *
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
        $data = BackendUtility::getRecord('pages',$pageUid,'uid,pid,tx_select_bas_page_mode');
        if($data['tx_select_bas_page_mode'] == 'mode 1' || $data['tx_select_bas_page_mode'] == 'mode 2')
            return true;
        // mode 3 ou mode 4
        else if($data['tx_select_bas_page_mode'] != '')
            return false;

        // check parentes page
        $pageUid = $data['pid'];
        while ($pageUid != 0 && $data['tx_select_bas_page_mode'] == ''){
            $data = BackendUtility::getRecord('pages',$pageUid,'uid,pid,tx_select_bas_page_mode');
            $pageUid = $data['pid'];
        }
        // if the parent page has the mode 1
        if($data['tx_select_bas_page_mode'] == 'mode 1')
            return true;
        else
            return false;

    }

}