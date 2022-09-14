<?php
defined('TYPO3') or die();
$lll = 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages',
    [
        'tx_select_bas_page_mode' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pgu_be/Resources/Private/Language/locallang_db.xlf:pages.tx_select_bas_page_mode',
            'description' => 'LLL:EXT:pgu_be/Resources/Private/Language/locallang_db.xlf:pages.tx_select_bas_page_mode.desc',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Non précisé', 'not specified', ''],
                    ['Afficher pour cette page et ses sous-pages', 'mode 1', ''],
                    ['Afficher pour cette page seulement', 'mode 2', ''],
                    ['Masquer pour cette page et ses sous-pages', 'mode 3', ''],
                    ['Masquer pour cette page seulement', 'mode 4', ''],
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
