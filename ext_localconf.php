<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(
    function () {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'QcComments',
            'commentsForm',
            [Qc\QcComments\Controller\Frontend\CommentsController::class => 'show,saveComment']
        );
    }
);
