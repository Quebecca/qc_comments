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


    public function setSettings(string $pluginName): void
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $this->settings =  $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            $pluginName
        );
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
        return $this->settings['recaptcha']['enabled'] == '1';
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
        return $this->settings['spamshield']['_enable'] == '1';
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function isMethodEnabled(string $methodName) : bool {
        return $this->settings['spamshield']['methods']["$methodName"]['_enable'] == '1';
    }

    public function getLinkCheckMethodLinksLimit(){
        return $this->settings['spamshield']['methods']['3']['configuration']['linkLimit'] ?? '1';

    }

    /**
     * @return bool
     */
    public function isAnonymizeCommentEnabled() : bool {
        return $this->settings['comments']['anonymizeComment']['enabled'] == '1';
    }

    /**
     * @return string
     */
    public function getAnonymizationCommentPattern() : string {
        return $this->settings['comments']['anonymizeComment']['pattern'] ?? '';
    }

}