<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die;

ExtensionManagementUtility::addStaticFile('qc_comments', 'Configuration/TypoScript', 'Module used to manage FE users comments on pages');