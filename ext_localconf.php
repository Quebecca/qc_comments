<?php
call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Qc.QcComments',
            'commentsForm',
            ['Comments' => 'submitForm'],
            []
        );


        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Qc.QcComments',
            'commentsForm',
            ['Comments' => 'show'],
            []
        );

    });
