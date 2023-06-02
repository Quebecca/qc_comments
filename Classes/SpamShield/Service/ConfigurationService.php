<?php

declare(strict_types=1);
namespace Qc\QcComments\SpamShield\Service;
/***
 *
 * This file is part of Qc Comments project.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 <techno@quebec.ca>
 *
 ***/
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility as CoreArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class ConfigurationService to get the typoscript configuration from qc_comments
 */
class ConfigurationService implements SingletonInterface
{
    /**
     * @var array
     */
    protected array $settings = [];

    /**
     * @var array
     */
    protected array $configuration = [];

    /**
     * @param string $pluginName
     * @return array
     */
    public function getTypoScriptSettings(string $pluginName = 'QcComments'): array
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        return $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            $pluginName,
        );
    }

    /**
     * Check if a given validation is turned on generally
     * and if there is a given spamshield method enabled
     *
     * @param array $settings
     * @param string $className
     * @return bool
     */
    public function isValidationEnabled(array $settings, string $className): bool
    {
        $validationActivated = false;
        if (CoreArrayUtility::isValidPath($settings, 'spamshield/methods')) {
            foreach ((array)$settings['spamshield']['methods'] as $method) {
                if (!empty($method['class'])
                    && !empty($method['_enable'])
                    && $method['class'] === $className
                    && $method['_enable'] === '1') {
                    $validationActivated = true;
                    break;
                }
            }
        }
        return !empty($settings['spamshield']['_enable']) && $validationActivated;
    }
}
