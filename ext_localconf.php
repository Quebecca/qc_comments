<?php

use Qc\QcComments\Controller\Frontend\CommentsController;
use Qc\QcComments\Controller\FrontendController;
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
            ], //With cash - prevent storing cashed data
            [
                CommentsController::class  => 'show,saveComment',
            ] // storing without using cash
        );
        ExtensionUtility::configurePlugin(
            'QcComments',
            'commentsFormAjax',
            [
                CommentsController::class => 'savePositifComment',
            ],
            [
                CommentsController::class => 'savePositifComment',
            ]
        );
    }
);

ExtensionManagementUtility::addUserTSConfig(
    "@import 'EXT:qc_comments/Configuration/TSconfig/pageconfig.tsconfig'"
);
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)
    ->registerIcon(
        'qc_comments',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:qc_comments/Resources/Public/Icons/qc_comments.svg']
    );
