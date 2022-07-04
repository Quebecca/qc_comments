<?php
defined('TYPO3') || die('Access denied.');

call_user_func(
    function () {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'QcComments',
            'Qccomments',
            'Add comments section in page'
        );

        if (TYPO3_MODE === 'BE') {

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'QcComments',
                'web', // Make module a submodule of 'web'
                'admin', // Submodule key
                '', // Position
                [
                    \Qc\QcComments\Controller\AdministrationController::class => 'stats,list, exportStats, exportList, resetFilter',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:qc_comments/Resources/Public/Icons/qc_comments.svg',
                    'labels' => 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:qc_comments',
//                    'navigationComponentId' => '',
                ]
            );

        }
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('qc_comments', 'Configuration/TypoScript', 'Module be gestion des commentaires');
    }
);
