<?php

declare(strict_types=1);

return [
    'export_comments' => [
        'path' => '/export_comments',
        'referrer' => 'required,refresh-empty',
        'target' => \Qc\QcComments\Controller\CommentsBEController::class . '::exportCommentsAction'
    ],

    'export_statistics' => [
        'path' => '/export_statistics',
        'referrer' => 'required,refresh-empty',
        'target' => \Qc\QcComments\Controller\StatisticsBEController::class . '::exportStatisticsAction'
    ],

];
