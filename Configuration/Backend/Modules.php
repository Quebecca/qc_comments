<?php
use Qc\QcComments\Controller\StatisticsBEController;
use Qc\QcComments\Controller\CommentsBEController;
use Qc\QcComments\Controller\v12\QcCommentsBEv12Controller;

return [
    'web_qc_comments' => [
        'parent' => 'web',
        'position' => ['after' => 'info'],
        'access' => 'user',
        'workspaces' => 'live',
        'extensionName' => 'QcComments',
        'path' => '/module/web/qc_comments',
        // todo, register icon 'EXT:brofix/Resources/Public/Icons/Extension.svg'
        //'iconIdentifier' => 'module-example',
        //'icon' => 'EXT:brofix/Resources/Public/Icons/Extension.svg',
        'navigationComponent' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
        'labels' => [
            'title' => 'Qc comments',
        ]
    ],
 /*   'web_qccomments_statistics' => [
        'parent' => 'web_qc_comments',
        'access' => 'user,group',
        'workspaces' => 'live',
        'path' => '/module/web/qc_comments/statistics',
        'labels' => [
            'title' => 'Statistics',
        ],
        'extensionName' => 'QcComments',
        'controllerActions' => [
            StatisticsBEController::class => [
                'statistics', 'exportStatistics', 'resetFilter'
            ]
        ],
        'routes' => [
            '_default' => [
                'target' => CommentsBEController::class . '::statisticsAction',
            ],
            'statistics' => [
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
        ],

    ],*/
/*    'web_qccomments_comments' => [
        'parent' => 'web_qc_comments',
        'access' => 'user,group',
        'workspaces' => 'live',
        'path' => '/module/web/qc_comments/comments',
        'labels' => [
            'title' => 'Comments',
        ],
        'navigationComponent' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
        'extensionName' => 'QcComments',
        'controllerActions' => [
            CommentsBEController::class => [
                'comments', 'exportComments', 'resetFilter'
            ],
        ],
        'routes' => [
            '_default' => [
                'target' => CommentsBEController::class . '::comments',
            ],
            'exportComments' => [
                'path' => '/exportComments',
                'target' => CommentsBEController::class . '::exportComments',
            ],
            'resetFilter_comments' => [
                'path' => '/resetFilter_comments',
                'target' => CommentsBEController::class . '::resetFilter',
            ]
        ]
    ],*/


    'web_qc_comments_stats' => [
        'parent' => 'web_qc_comments',
        'access' => 'user',
        'path' => '/module/web/qc_comments/statistics',
        'iconIdentifier' => 'qc_comments',
        'labels' => [
            'title' => 'Statistics',
        ],
        'extensionName' => 'QcComments',
        'controllerActions' => [
            QcCommentsBEv12Controller::class => [
                'statistics','comments','resetFilter'
            ],

        ],
        'moduleData' => [
            'action' => 'statistics',
        ],
    ],
/*
    'web_qc_comments_comments' => [
        'parent' => 'web_qc_comments',
        'access' => 'user',
        'path' => '/module/web/qc_comments/comments',
        'iconIdentifier' => 'qc_comments',
        'labels' => [
            'title' => 'Comments',
        ],
        'routes' => [
            '_default' => [
                'target' => CommentsBEController::class . '::handleRequest',
            ],
        ],
        'moduleData' => [
            'action' => 'comments',
        ],
    ],*/
];