<?php

declare(strict_types=1);

use Qc\QcComments\Controller\Backend\QcCommentsBEController;

return [
    'export_comments' => [
        'path' => '/export_comments',
        'referrer' => 'required,refresh-empty',
        'target' => QcCommentsBEController::class . '::exportCommentsAction'
    ],

    'export_statistics' => [
        'path' => '/export_statistics',
        'referrer' => 'required,refresh-empty',
        'target' => QcCommentsBEController::class . '::exportStatisticsAction'
    ],

];
