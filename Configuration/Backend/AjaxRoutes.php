<?php

declare(strict_types=1);

use Qc\QcComments\Controller\CommentsBEController;
use Qc\QcComments\Controller\StatisticsBEController;

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
