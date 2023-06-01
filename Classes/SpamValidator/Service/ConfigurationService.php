<?php

declare(strict_types=1);
namespace Qc\QcComments\SpamValidator\Service;

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
            'QcComments',
        );
    }

    /**
     * Get configuration (formally known as $this->conf in oldschool extensions)
     *
     * @param string $pluginName
     * @return array
     */
    public function getTypoScriptConfiguration(string $pluginName = 'QcComments'): array
    {
        if (empty($this->configuration[$pluginName])) {
            $this->configuration[$pluginName] = $this->getTypoScriptConfigurationFromOverallConfiguration($pluginName);
        }
        return $this->configuration[$pluginName];
    }

    /**
     * @param string $pluginName
     * @return array
     */
    protected function getTypoScriptSettingsFromOverallConfiguration(string $pluginName): array
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $setup = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'QcComments',
            $pluginName
        );

        return $setup['setup'] ?? [];
    }

    /**
     * @param string $pluginName
     * @return array
     */
    protected function getTypoScriptConfigurationFromOverallConfiguration(string $pluginName): array
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $configuration = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
            'QcComments',
            $pluginName
        );
        if (ArrayUtility::isValidPath($configuration, 'plugin./tx_powermail./settings./setup.')) {
            return (array)$configuration['plugin.']['tx_qccomments.']['settings.']['setup.'];
        }
        return [];
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
