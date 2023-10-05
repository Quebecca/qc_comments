<?php
use Qc\QcComments\Controller\Backend\QcCommentsBEController;

return [
    'web_qc_comments' => [
        'parent' => 'web',
        'position' => ['after' => 'info'],
        'path' => '/module/web/qc_comments',
        'icon' => 'EXT:qc_comments/Resources/Public/Icons/qc_comments.svg',
        'labels' => [
            'title' => 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:qc_comments',
        ],
        'extensionName' => 'QcComments',
        'controllerActions' => [
            QcCommentsBEController::class => [
                'handleRequests',
                'resetFilter',
                'comments',
                'statistics'
            ]
        ]
    ],
];