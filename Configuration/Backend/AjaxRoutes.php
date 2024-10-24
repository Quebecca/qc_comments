<?php

declare(strict_types=1);

use Qc\QcComments\Controller\Backend\CommentsBEController;
use Qc\QcComments\Controller\Backend\StatisticsBEController;
use Qc\QcComments\Controller\Backend\TechnicalProblemsBEController;

return [
    'export_comments' => [
        'path' => '/export_comments',
        'referrer' => 'required,refresh-empty',
        'target' => CommentsBEController::class . '::exportCommentsAction'
    ],

    'export_statistics' => [
        'path' => '/export_statistics',
        'referrer' => 'required,refresh-empty',
        'target' => StatisticsBEController::class . '::exportStatisticsAction'
    ],

    'problem_fixed' => [
        'path' => '/problem_fixed',
        'referrer' => 'required, refresh-empty',
        'target' => TechnicalProblemsBEController::class. '::technicalProblemFixedAction'
    ],
    'export_technicalProblems' => [
        'path' => '/export_technicalProblems',
        'referrer' => 'required,refresh-empty',
        'target' => TechnicalProblemsBEController::class . '::exportTechnicalProblemsAction'
    ]

];
