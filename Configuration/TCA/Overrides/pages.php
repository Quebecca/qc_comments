<?php

defined('TYPO3') || die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {
    $lll = 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:';
    $newFields = [
        'tx_comments_form_mode' => [
            'exclude' => 1,
            'label' => $lll . 'pages.tx_comments_form_mode',
            'description' => $lll . 'pages.tx_comments_form_mode.desc',
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
    ];
    ExtensionManagementUtility::addTCAcolumns('pages', $newFields);
    ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_comments_form_mode'
    );
});
