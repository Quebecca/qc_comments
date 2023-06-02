<?php

declare(strict_types=1);
namespace Qc\QcComments\ViewHelpers\SpamShieldValidation;

use Qc\QcComments\SpamShield\Service\ConfigurationService;
use Qc\QcComments\SpamShield\Methods\HoneyPodMethod;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class IsHonepodEnabledViewHelper
 */
class IsHonepodEnabledViewHelper extends AbstractViewHelper
{
    /**
     * @return bool
     */
    public function render(): bool
    {
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $settings = $configurationService->getTypoScriptSettings();
        return $configurationService->isValidationEnabled($settings, HoneyPodMethod::class);
    }


}
