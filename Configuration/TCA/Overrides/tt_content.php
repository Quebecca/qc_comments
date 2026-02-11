<?php

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die;

ExtensionUtility::registerPlugin(
    'QcComments',
    'commentsForm',
    'LLL:EXT:qc_comments/Resources/Private/Language/locallang_be.xlf:plugin.comments.title',
    'qc_comments',
    'comments',
    'LLL:EXT:qc_comments/Resources/Private/Language/locallang_be.xlf:plugin.comments.description',
);