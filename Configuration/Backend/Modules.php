<?php
use Qc\QcComments\Controller\StatisticsBEController;
use Qc\QcComments\Controller\CommentsBEController;
use Qc\QcComments\Controller\v12\QcCommentsBEv12Controller;

return [
    'web_qc_comments' => [
        'parent' => 'web',
        'position' => ['after' => 'info'],
        'path' => '/module/web/qc_comments/statistics',
        'icon' => 'EXT:qc_comments/Resources/Public/Icons/qc_comments.svg',
        'labels' => [
            'title' => 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:qc_comments',
        ],
        'extensionName' => 'QcComments',
        'controllerActions' => [
            QcCommentsBEv12Controller::class => [
                'comments','statistics','resetFilter'
            ]
        ]
    ],
];