<?php
declare(strict_types=1);

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

namespace Qc\QcComments\Configuration;

use TYPO3\CMS\Core\Utility\ArrayUtility as CoreArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class TyposcriptConfiguration
{
    /**
     * configuration type is used to Use "CONFIGURATION_TYPE_SETTINGS" is we have a FE call or "CONFIGURATION_TYPE_FRAMEWORK" if we have a BE call
     * @var string
     */
    protected string $configurationType = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS;

    public function __construct()
    {
        $this->setSettings('QcComments');
    }

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
     * @return void
     */

    public function setSettings(string $pluginName): void
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $this->settings =  $configurationManager->getConfiguration(
            $this->configurationType,
            $pluginName
        );
    }

    /**
     * @param string $configurationType
     */
    public function setConfigurationType(string $configurationType): void
    {
        $this->configurationType = $configurationType;
    }

    /**
     * @return string
     */
    public function getConfigurationType(): string
    {
        return $this->configurationType;
    }

    /**
     * @return array
     */
    public function getTypoScriptSettings(): array
    {
        return $this->settings;
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


    /**
     * @return int
     */
    public function getCommentsMaxCharacters() : int {
        return intval($this->settings['comments']['maxCharacters']);
    }


    public function getCommentsMinCharacters() {
        return $this->settings['comments']['minCharacters'];
    }

    /**
     * @return bool
     */
    public function isRecaptchaEnabled(): bool {
        return ($this->settings['recaptcha']['enabled'] ?? false) == '1';
    }

    public function getRecaptchaSitekey(){
        return $this->settings['recaptcha']['sitekey'] ?? '';

    }

    public function getRecaptchaSecretKey(){
        return $this->settings['recaptcha']['secret'] ?? '';

    }

    /**
     * @return bool
     */
    public function isSpamShieldEnabled() : bool {
        return ($this->settings['spamshield']['_enable'] ?? false) == '1';
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function isMethodEnabled(string $methodName) : bool {
        return ($this->settings['spamshield']['methods']["$methodName"]['_enable'] ?? false) == '1';
    }

    public function getLinkCheckMethodLinksLimit(){
        return $this->settings['spamshield']['methods']['3']['configuration']['linkLimit'] ?? '1';

    }

    /**
     * @return bool
     */
    public function isAnonymizeCommentEnabled() : bool {
        return ($this->settings['comments']['anonymizeComment']['enabled'] ?? false) == '1';
    }

    /**
     * @return string
     */
    public function getAnonymizationCommentPattern() : string {
        return $this->settings['comments']['anonymizeComment']['pattern'] ?? '';
    }

    public function getReasonOptions($lang) : array {
        return $this->settings['options'][$lang] ?? [];
    }

    public function getNegativeCommentsReasonsForBE() :array {
        return $this->settings['plugin.']['tx_qccomments.']['settings.']['options.']['negative_reasons.'] ??  [];
    }
}
