<?php


use Qc\QcComments\Controller\AdministrationController;
use Qc\QcComments\Controller\Backend\CommentsTabController;
use Qc\QcComments\Controller\Backend\QcBackendModuleActionController;
use Qc\QcComments\Controller\Backend\StatisticsTabController;

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
                    QcBackendModuleActionController::class => 'resetFilter',
                    StatisticsTabController::class => 'statistics, , exportStatistics, , ',
                    CommentsTabController::class => 'comments, exportComments',
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
