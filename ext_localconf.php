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
            [CommentsController::class => 'show,saveComment'], //With cash - prevent storing cashed data
            [CommentsController::class  => 'show,saveComment'] // storing without using cash
        );
    }

);

ExtensionManagementUtility::addUserTSConfig(
    "@import 'EXT:qc_comments/Configuration/TSconfig/pageconfig.tsconfig'"
);
