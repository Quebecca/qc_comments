<?php

use Qc\QcComments\Controller\Backend\CommentsBEController;
use Qc\QcComments\Controller\Backend\HiddenCommentsBEController;
use Qc\QcComments\Controller\Backend\QcCommentsBEController;
use Qc\QcComments\Controller\Backend\StatisticsBEController;
use Qc\QcComments\Controller\Backend\TechnicalProblemsBEController;

return [
    'web_qc_comments' => [
        'parent' => 'web',
        'position' => ['after' => 'info'],
        'access' => 'user',
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
            ],
            StatisticsBEController::class => [
                'statistics'
            ],
            CommentsBEController::class => [
                'comments',
                'deleteComment',
                'hideComment'
            ],
            HiddenCommentsBEController::class => [
                'hiddenComments',
                'deleteComment'
            ],
            TechnicalProblemsBEController::class => [
                'technicalProblems',
                'markProblemAsFixed',
                'deleteTechnicalProblems'
            ]
        ]
    ],
];
