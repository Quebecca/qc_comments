<?php

use Qc\QcComments\Controller\CommentsBEController;
use Qc\QcComments\Controller\StatisticsBEController;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

ExtensionUtility::registerPlugin(
    'QcComments',
    'commentsForm',
    'Show comments form'
);

call_user_func(
    function () {
        ExtensionUtility::registerPlugin(
            'QcComments',
            'commentsForm',
            'Add comments section in page'
        );
        ExtensionManagementUtility::addStaticFile('qc_comments', 'Configuration/TypoScript', 'Module used to manage FE users comments on pages');
    }
);
