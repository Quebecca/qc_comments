<?php

declare(strict_types=1);
namespace Qc\QcComments\SpamValidator;

use Exception;
use In2code\Powermail\Utility\ConfigurationUtility;
use In2code\Powermail\Utility\ObjectUtility;
use Qc\QcComments\SpamValidator\AbstractValidator;
use Qc\QcComments\Domain\Model\Comment;
use Qc\QcComments\SpamValidator\SpamShield\AbstractMethod;
use Qc\QcComments\SpamValidator\SpamShield\MethodInterface;
use Qc\QcComments\SpamValidator\SpamShield\ValueBlacklistMethod;
use Qc\QcComments\SpamValidator\SpamValidator\Exceptions\ClassDoesNotExistException;
use Qc\QcComments\SpamValidator\SpamValidator\Exceptions\InterfaceNotImplementedException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator as ExtbaseAbstractValidator;

/**
 * Class SpamShieldValidator
 */
class SpamShieldValidator extends AbstractValidator
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
     * Error messages for email to admin
     *
     * @var array
     */
    protected array $messages = [];

    /**
     * @var string
     */
    protected string $methodInterface = MethodInterface::class;

    /**
     * @param Comment $comment
     * @return bool
     * @throws Exception
     */
    public function isValid($comment)
    {
       if ($this->isSpamShieldEnabled()) {
           $this->runAllSpamMethods($comment);
           // @todo : impelement the indicator
          /* if(!empty($this->messages)){
               foreach ($this->messages as $message){
                   $this->addError($message, 1580681599);
               }
           }*/
           /*$this->runAllSpamMethods($mail);
           $this->calculateMailSpamFactor();
           $this->saveSpamFactorInSession();
           $this->saveSpamPropertiesInDevelopmentLog();
           if ($this->isSpamToleranceLimitReached()) {
               $this->addError('spam_details', 1580681599, ['spamfactor' => $this->getCalculatedSpamFactor(true)]);
               $this->setValidState(false);
               $this->sendSpamNotificationMail($mail);
               $this->logSpamNotification($mail);
           }*/
           if(!empty($this->messages)){
               $this->addError('spam_details', 1580681599);
           }
        }
        return $this->isValidState();
    }

    /**
     * @param Comment $comment
     * @return void
     * @throws Exception
     */
    protected function runAllSpamMethods(Comment $comment): void
    {
  /*          $valueBlacklistMethod = GeneralUtility::makeInstance(ValueBlacklistMethod::class,
            $comment,
            [],
            []
        );
        $valueBlacklistMethod->initialize();
        $valueBlacklistMethod->initializeSpamCheck();
        if($valueBlacklistMethod->spamCheck($comment) == true){
            $this->addMessage('ValueBlacklistMethod' . ' failed');
            $this->addError('spam_details', 1580681599);

        }*/

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
                    'Class ' . $method['class'] . ' does not exists - check if file was loaded with autoloader',
                    1578609568
                );
            }
            if (is_subclass_of($method['class'], $this->methodInterface)) {
                /** @var AbstractMethod $methodInstance */
                $methodInstance = GeneralUtility::makeInstance(
                    $method['class'],
                    $comment,
                    $this->settings,
                    $method['configuration']
                );
                $methodInstance->initialize();
                $methodInstance->initializeSpamCheck();
                if ((int)$method['indication'] > 0 && $methodInstance->spamCheck($comment)) {
                    $this->addMessage($method['name'] . ' failed');
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
     * Get all spamshield method classes from typoscript and sort them
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
        //$this->initializeObject();
       /* $breakerRunner = GeneralUtility::makeInstance(
            BreakerRunner::class,
            $mail,
            $this->settings,
            $this->flexForm
        );*/
        return !empty($this->settings['spamshield']['_enable']);
    }


}
