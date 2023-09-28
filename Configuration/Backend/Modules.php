<?php
use Qc\QcComments\Controller\StatisticsBEController;
use Qc\QcComments\Controller\CommentsBEController;
use Qc\QcComments\Controller\v12\QcCommentsBEv12Controller;

return [
    'web_qc_comments' => [
        'parent' => 'web',
        'access' => 'user',
        'position' => ['after' => 'info'],
        'path' => '/module/web/qc_comments/statistics',
        'workspaces' => 'live',
        'iconIdentifier' => 'qc_comments',
        'icon' => 'EXT:qc_comments/Resources/Public/Icons/Extension.svg',
        'labels' => [
            'title' => 'Statistics',
        ],
        'extensionName' => 'QcComments',
        'controllerActions' => [
            QcCommentsBEv12Controller::class => [
                'comments','statistics','resetFilter'
            ],

        ],
        'moduleData' => [
            'action' => 'statistics',
        ],
    ],
];