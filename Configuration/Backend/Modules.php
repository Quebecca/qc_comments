<?php
use Qc\QcComments\Controller\StatisticsBEController;
use Qc\QcComments\Controller\CommentsBEController;

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
                'statistics', 'exportStatistics', 'resetFilter'
            ],
            CommentsBEController::class => [
                'comments', 'exportComments', 'resetFilter'
            ],
        ],
    ],
    'routes' => [
        '_default' => [
            'target' => StatisticsBEController::class . '::statistics',
        ],
        'exportStatistics' => [
            'path' => '/exportStatistics',
            'target' => StatisticsBEController::class . '::exportStatistics',
        ],
        'resetFilter' => [
            'target' => StatisticsBEController::class . '::resetFilter',
            'methods' => ['POST'],
        ],
        'exportComments' => [
            'path' => '/exportComments',
            'target' => CommentsBEController::class . '::exportComments',
        ],
        'resetFilter_comments' => [
            'path' => '/resetFilter_comments',
            'target' => CommentsBEController::class . '::resetFilter',
        ],
        'comments' => [
            'path' => '/comments',
            'target' => CommentsBEController::class . '::comments',
        ]
],
];