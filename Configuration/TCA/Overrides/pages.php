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
                    [
                        'label' => $lll . 'pages.notSpecified',
                        'value' => 'not specified',
                    ],
                    [
                        'label' => $lll . 'pages.mode1',
                        'value' => 'mode 1',
                    ],
                    [
                        'label' => $lll . 'pages.mode2',
                        'value' => 'mode 2',
                    ],
                    [
                        'label' => $lll . 'pages.mode3',
                        'value' => 'mode 3',
                    ],
                    [
                        'label' => $lll . 'pages.mode4',
                        'value' => 'mode 4',
                    ],
                ],
            ],
        ],
    ];
    ExtensionManagementUtility::addTCAcolumns('pages', $newFields);
    ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_comments_form_mode'
    );
});
