<?php

declare(strict_types=1);

use Qc\QcComments\Controller\Backend\CommentsBEController;
use Qc\QcComments\Controller\Backend\StatisticsBEController;

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

];
