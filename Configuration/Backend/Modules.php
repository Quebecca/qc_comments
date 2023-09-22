<?php

return [
    'web_qccomments' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user,group',
        'workspaces' => 'live',
        'path' => '/module/page/example',
        'labels' => 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:qc_comments',
        'extensionName' => 'QcComments',
        'controllerActions' => [
            StatisticsBEController::class => [
                'statistics, exportStatistics, resetFilter',
            ],
            CommentsBEController::class => [
                'comments, exportComments, resetFilter',
            ],
        ],
    ]
];