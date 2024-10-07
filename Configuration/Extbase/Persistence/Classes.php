<?php
declare(strict_types = 1);

use Qc\QcComments\Domain\Model\Comment;
return [
    Comment::class => [
        'tableName' => 'tx_qccomments_domain_model_comment',
        'date_hour' => 'dateHour',
        'url_orig' => 'urlOrig',
        'uid_orig' => 'uidOrig',
        'uid_perms_group' => 'uidPermsGroup',
        'reason_code' => 'reasonCode',
        'reason_long_label' => 'reasonLongLabel',
        'reason_short_label' => 'reasonShortLabel',
        'submitted_form_uid' => 'submittedFormUid'
    ],
];
