<?php
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
        return $this->getCommentsFormPageMode($this->arguments['pageUid']);
    }

    /**
     * This function is used to check if the FE page will show the comments form
     * @param int $pageUid
     * @return bool
     */
    public function getCommentsFormPageMode(int $pageUid): bool
    {
        $data = BackendUtility::getRecord(
            'pages',
            $pageUid,
            'uid,pid,tx_select_comments_form_page_mode'
        );
        $enabledMode = ['mode 1', 'mode 2'];
        $disabledMode = ['mode 3', 'mode 4'];
        $inheritanceMode = ['', 'not specified'];
        $currentMode = $data['tx_select_comments_form_page_mode'];

        if (in_array($currentMode, $enabledMode)
            || in_array($currentMode, $disabledMode))
        {
            return in_array($currentMode, $enabledMode);
        }

        // check parents page
        $pageUid = $data['pid'];
        while ($pageUid != 0 && in_array($currentMode, $inheritanceMode)) {
            $data = BackendUtility::getRecord(
                'pages',
                $pageUid,
                'uid,pid,tx_select_comments_form_page_mode');
            $currentMode = $data['tx_select_comments_form_page_mode'];
            $pageUid = $data['pid'];
        }
        return $currentMode == 'mode 1';
    }
}
