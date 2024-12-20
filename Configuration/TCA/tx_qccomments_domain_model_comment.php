<?php

return [
    'ctrl' => [
        'title' => 'Comments',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'typeicon_classes' => [
            'default' => 'qc_comments'
        ],
        'delete' => 'deleted',
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => ['type' => 'language']
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 0,
                'items' => [
                    ['', 0],
                ],
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.enabled'

                    ]
                ],
            ],
        ],
        'deleted' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.deleted',
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.enabled'

                    ]
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 13,
                'eval' => 'datetime',
                'default' => 0,
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 13,
                'eval' => 'datetime',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
            ],
        ],
        'comment' => [
            'exclude' => true,
            'label' => 'Comment message',
            'config' => [
                'type' => 'input',
            ],
        ],
        'useful' => [
            'exclude' => true,
            'label' => 'Useful',
            'config' => [
                'type' => 'input',
            ],
        ],
        'url_orig' => [
            'exclude' => true,
            'label' => 'url_orig',
            'config' => [
                'type' => 'input',
            ],
        ],
        'uid_Orig' => [
            'exclude' => true,
            'label' => 'uid_orig',
            'config' => [
                'type' => 'input',
            ],
        ],
        'uid_perms_group' => [
            'exclude' => true,
            'label' => 'uid_perms_group',
            'config' => [
                'type' => 'input',
            ],
        ],
        'date_hour' => [
            'exclude' => true,
            'label' => 'date_hour',
            'config' => [
                'type' => 'input',
            ],
        ],
        'reason_code' => [
            'exclude' => true,
            'label' => 'reason',
            'config' => [
                'type' => 'input',
            ],
        ],
        'reason_long_label' => [
            'exclude' => true,
            'label' => 'reason_long_label',
            'config' => [
                'type' => 'input',
            ],
        ],
        'reason_short_label' => [
            'exclude' => true,
            'label' => 'reason_short_label',
            'config' => [
                'type' => 'input',
            ],
        ],
        'submitted_form_uid' => [
            'exclude' => true,
            'label' => 'submitted_form_uid',
            'config' => [
                'type' => 'input',
            ],
        ],
        'deleted_by_user_uid' => [
            'exclude' => true,
            'label' => 'deleted_by_user_uid',
            'config' => [
                'type' => 'input',
            ],
        ],
        'deleting_date' => [
            'exclude' => true,
            'label' => 'deleting_date',
            'config' => [
                'type' => 'input',
            ],
        ],
        'hidden_comment' => [
            'exclude' => true,
            'label' => 'hidden_comment',
            'config' => [
                'type' => 'input',
            ],
        ],
        'hidden_by_user_uid' => [
            'exclude' => true,
            'label' => 'hidden_by_user_uid',
            'config' => [
                'type' => 'input',
            ],
        ],
        'hidden_date' => [
            'exclude' => true,
            'label' => 'hidden_date',
            'config' => [
                'type' => 'input',
            ],
        ],
        'fixed' => [
            'exclude' => true,
            'label' => 'fixed',
            'config' => [
                'type' => 'input',
            ],
        ],
        'fixed_by_user_uid' => [
            'exclude' => true,
            'label' => 'fixed_by_user_uid',
            'config' => [
                'type' => 'input',
            ],
        ],
        'fixed_date' => [
            'exclude' => true,
            'label' => 'fixed_date',
            'config' => [
                'type' => 'input',
            ],
        ],
    ],
];
