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
        'submitted_form_uid' => 'submittedFormUid',
        'deleted_by_user_uid' => 'deletedByUserUid',
        'deleting_date' => 'deletingDate',
        'hidden_comment' => 'hiddenComment',
        'hidden_by_user_uid' => 'hiddenByUserUid',
        'hidden_date' => 'hiddenDate',
        'fixed' => 'fixed',
        'fixed_by_user_uid' => 'fixedByUserUid',
        'fixed_date' => 'fixedDate'
    ],
];
