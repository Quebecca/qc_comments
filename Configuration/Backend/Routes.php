<?php
declare(strict_types=1);
use Qc\QcInfoRights\Controller;

/**
 * Definitions for routes provided by EXT:backend
 * Contains Route to Export Lists of backend User
 */
return [
    //Backend Route link To Export Backend user list
    'export_comments' => [
        'path' => '/export_comments',
        'referrer' => 'required,refresh-empty',
        'target' =>  \Qc\QcComments\Controller\CommentsTabController::class . '::exportCommentsAction'
    ],


];
