<?php

declare(strict_types=1);
namespace Qc\QcComments\ViewHelpers\SpamShieldValidation;

use Qc\QcComments\Configuration\TyposcriptConfiguration;
use Qc\QcComments\SpamShield\Methods\HoneyPotMethod;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class IsHoneypotEnabledViewHelper
 */
class IsHoneypotEnabledViewHelper extends AbstractViewHelper
{
    /**
     * @return bool
     */
    public function render(): bool
    {
        $configurationService = GeneralUtility::makeInstance(TyposcriptConfiguration::class);
        $settings = $configurationService->getTypoScriptSettings();
        return $configurationService->isValidationEnabled($settings, HoneyPotMethod::class);
    }


}
