<?php


use Qc\QcComments\Controller\CommentsTabController;
use Qc\QcComments\Controller\QcBackendModuleController;
use Qc\QcComments\Controller\StatisticsTabController;

defined('TYPO3') || die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'QcComments', 'commentsForm', 'Show comments form'
);
/*\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'QcComments', 'submittedForm', 'Comments form is submitted'
);*/

call_user_func(
    function () {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Qc.QcComments',
            'commentsForm',
            'Add comments section in page'
        );

        if (TYPO3_MODE === 'BE') {

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'QcComments',
                'web',
                'admin',
                '',
                [
                    StatisticsTabController::class => 'statistics, , exportStatistics, , ',
                    CommentsTabController::class => 'comments, exportComments',
                    QcBackendModuleController::class => 'resetFilter',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:qc_comments/Resources/Public/Icons/qc_comments.svg',
                    'labels' => 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:qc_comments',
                ]
            );

        }
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('qc_comments', 'Configuration/TypoScript', 'Module used to manage FE users comments on pages');
    }
);
