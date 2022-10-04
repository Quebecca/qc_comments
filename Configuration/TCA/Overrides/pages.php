<?php

defined('TYPO3') or die();
$lll = 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages',
    [
        'tx_select_bas_page_mode' => [
            'exclude' => 1,
            'label' => $lll . 'pages.tx_select_bas_page_mode',
            'description' => $lll . 'pages.tx_select_bas_page_mode.desc',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$lll . 'pages.notSpecified', 'not specified', ''],
                    [$lll . 'pages.mode1', 'mode 1', ''],
                    [$lll . 'pages.mode2', 'mode 2', ''],
                    [$lll . 'pages.mode3', 'mode 3', ''],
                    [$lll . 'pages.mode4', 'mode 4', ''],
                ],
            ],
        ],
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'commentsSection',
    'tx_select_bas_page_mode'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--palette--;;commentsSection',
    '',
    'after:tx_pgu_description_navigation'
);
