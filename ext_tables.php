<?php

defined('TYPO3') || die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'QcComments',
    'commentsForm',
    'Show comments form'
);

call_user_func(
    function () {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'QcComments',
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
                    \Qc\QcComments\Controller\StatisticsTabController::class => 'statistics, exportStatistics, resetFilter',
                    \Qc\QcComments\Controller\CommentsTabController::class => 'comments, exportComments, resetFilter',
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
