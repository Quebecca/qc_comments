<?php

use Qc\QcComments\Controller\Frontend\CommentsController;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(
    function () {
        ExtensionUtility::configurePlugin(
            'QcComments',
            'commentsForm',
            [
                CommentsController::class => 'show,saveComment',
            ],
            [],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );
        ExtensionUtility::configurePlugin(
            'QcComments',
            'commentsFormAjax',
            [
                CommentsController::class => 'savePositifComment',
            ],
            [],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );
    }
);

ExtensionManagementUtility::addUserTSConfig(
    "@import 'EXT:qc_comments/Configuration/user.tsconfig'"
);
