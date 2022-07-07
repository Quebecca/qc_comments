<?php
// @todo : TYPO3 die...
call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Qc.QcComments',
            'commentsForm',
            [Qc\QcComments\Controller\Frontend\CommentsController::class => 'show,addComment'], //With cash - prevent storing cashed data
            [Qc\QcComments\Controller\Frontend\CommentsController::class  => 'show,addComment'] // storing without passing by cash
        );

    });
