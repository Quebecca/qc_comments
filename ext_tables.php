<?php


use Qc\QcComments\Controller\CommentsTabController;
use Qc\QcComments\Controller\StatisticsTabController;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

ExtensionUtility::registerPlugin(
    'QcComments', 'commentsForm', 'Show comments form'
);


call_user_func(
    function () {

        ExtensionUtility::registerPlugin(
            'Qc.QcComments',
            'commentsForm',
            'Add comments section in page'
        );

        if (TYPO3_MODE === 'BE') {

            ExtensionUtility::registerModule(
                'QcComments',
                'web',
                'admin',
                '',
                [
                    StatisticsTabController::class => 'statistics, exportStatistics, resetFilter',
                    CommentsTabController::class => 'comments, exportComments, resetFilter',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:qc_comments/Resources/Public/Icons/qc_comments.svg',
                    'labels' => 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:qc_comments',
                ]
            );

        }
        ExtensionManagementUtility::addStaticFile('qc_comments', 'Configuration/TypoScript', 'Module used to manage FE users comments on pages');
    }
);
