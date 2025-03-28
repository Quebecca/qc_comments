<?php

declare(strict_types=1);
namespace Qc\QcComments\SpamShield;
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
use Exception;
use Qc\QcComments\Configuration\TyposcriptConfiguration;
use Qc\QcComments\Domain\Model\Comment;
use Qc\QcComments\SpamShield\Methods\AbstractMethod;
use Qc\QcComments\SpamShield\Exceptions\ClassDoesNotExistException;
use Qc\QcComments\SpamShield\Exceptions\InterfaceNotImplementedException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator as ExtbaseAbstractValidator;

/**
 * Class SpamShieldValidator
 */
class SpamShieldValidator extends ExtbaseAbstractValidator
{

    /**
     * Spam indication
     *
     * @var int
     */
    protected int $spamIndicator = 0;

    /**
     * Spam tolerance limit
     *
     * @var float
     */
    protected float $spamFactorLimit = 1.0;

    /**
     * Calculated spam factor
     *
     * @var float
     */
    protected float $calculatedSpamFactor = 0.0;

    /**
     * @var array
     */
    protected array $messages = [];

    /**
     * @var array
     */
    protected array $settings;

    /**
     * @var string
     */
    protected string $methodInterface = AbstractMethod::class;

    /**
     * @var TyposcriptConfiguration
     */
    protected TyposcriptConfiguration  $typoscriptConfiguration;


    /**
     * Constructs the validator and sets validation options
     *
     * @param array $options Options for the validator
     * @throws InvalidValidationOptionsException
     */
   /* public function __construct(array $options = [])
    {
        parent::__construct($options);


    }*/
    /**
     * @param Comment $comment
     * @throws Exception
     */
    public function isValid($comment) : void
    {
        $this->typoscriptConfiguration
            = new TyposcriptConfiguration();
        $this->settings = $this->typoscriptConfiguration->getTypoScriptSettings();

       if ($this->typoscriptConfiguration->isSpamShieldEnabled()) {
           $this->runAllSpamMethods($comment);
           $this->calculateSpamFactor();
           if(!empty($this->messages)){
               $this->addError(
                   'spam_details',
                   (int)$this->getCalculatedSpamFactor(true)
               );
           }
        }
    }

    /**
     * @param Comment $comment
     * @return void
     * @throws Exception
     */
    protected function runAllSpamMethods(Comment $comment): void
    {
        foreach ($this->getSpamShieldMethodClasses() as $method) {
            $this->runSingleSpamMethod($comment, $method);
        }
    }

    /**
     * Run a single spam prevention method
     * @param Comment $comment
     * @param array $method
     * @throws ClassDoesNotExistException
     * @throws InterfaceNotImplementedException
     */
    protected function runSingleSpamMethod(Comment $comment, array $method = []): void
    {
        if (!empty($method['_enable'])) {
            if (!class_exists($method['class'])) {
                throw new ClassDoesNotExistException(
                    'Class '
                    . $method['class']
                    . ' does not exists - check if file was loaded with autoloader',
                    1578609568
                );
            }
            if (is_subclass_of($method['class'], $this->methodInterface)) {
                /** @var AbstractMethod $methodInstance */
                $methodInstance = GeneralUtility::makeInstance(
                    $method['class'],
                    $comment,
                    $this->settings,
                    $method['configuration'] ?? []
                );
                if ((int)$method['indication'] > 0 && $methodInstance->spamCheck($comment)) {
                    $this->addMessage($method['name'] . ' failed');
                    $this->increaseSpamIndicator((int)$method['indication']);

                }
            } else {
                throw new InterfaceNotImplementedException(
                    'Spam method does not implement ' . $this->methodInterface,
                    1578609554
                );
            }
        }

    }


    /**
     * @param bool $readableOutput
     * @return float|string
     */
    public function getCalculatedSpamFactor(bool $readableOutput = false)
    {
        $calculatedSpamFactor = $this->calculatedSpamFactor;
        if ($readableOutput) {
            $calculatedSpamFactor = $this->formatSpamFactor($calculatedSpamFactor);
        }
        return intval($this->calculatedSpamFactor);

    }

    /**
     * Format for Spamfactor (0.23 => 23%)
     *
     * @param float $factor
     * @return string
     */
    protected function formatSpamFactor(float $factor): string
    {
        return number_format(($factor * 100), 0) . '%';
    }

    /**
     * calculate spam factor for this mail
     *        spam formula with asymptote 1 (100%)
     *
     * @return void
     */
    protected function calculateSpamFactor(): void
    {
        $calculatedSpamFactor = 0;
        if ($this->getSpamIndicator() > 0) {
            $calculatedSpamFactor = -1 / $this->getSpamIndicator() + 1;
        }
        $this->setCalculatedSpamFactor($calculatedSpamFactor);
    }

    /**
     * @param float $calculatedSpamFactor
     * @return void
     */
    public function setCalculatedSpamFactor(float $calculatedSpamFactor): void
    {
        $this->calculatedSpamFactor = $calculatedSpamFactor;
    }


    /**
     * Increase Global Indicator
     *
     * @param int $indication
     * @return void
     */
    public function increaseSpamIndicator(int $indication): void
    {
        $this->setSpamIndicator($this->getSpamIndicator() + $indication);
    }


    /**
     * @param int $spamIndicator
     * @return void
     */
    public function setSpamIndicator(int $spamIndicator): void
    {
        $this->spamIndicator = $spamIndicator;
    }

    /**
     * @return int
     */
    public function getSpamIndicator(): int
    {
        return $this->spamIndicator;
    }

    /**
     * Get all spam shield method classes from typoscript and sort them
     *
     * @return array
     */
    protected function getSpamShieldMethodClasses(): array
    {
        $methods = (array)$this->settings['spamshield']['methods'];
        ksort($methods);
        return $methods;
    }


    /**
     * @param string $message
     * @return void
     */
    public function addMessage(string $message): void
    {
        $messages = $this->getMessages();
        $messages[] = $message;
        $this->setMessages($messages);
    }

    /**
     * @param array $messages
     * @return void
     */
    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function isSpamShieldEnabled(): bool
    {
        return !empty($this->settings['spamshield']['_enable']);
    }


}
