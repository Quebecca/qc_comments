<?php

declare(strict_types=1);
namespace Qc\QcComments\SpamValidator\SpamShield;

use In2code\Powermail\Utility\FrontendUtility;
use Qc\QcComments\Domain\Model\Comment;
use Qc\QcComments\SpamValidator\SpamValidator\Mail;

/**
 * Class AbstractMethod
 */
abstract class AbstractMethod implements MethodInterface
{

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var Comment
     */
    protected Comment $comment;

    /**
     * @param Comment $comment
     * @param array $settings
     * @param array $configuration
     */
    public function __construct(Comment $comment, array $settings, array $configuration = [])
    {
        $this->comment = $comment;
        $this->settings = $settings;
        $this->configuration = $configuration;
        $this->arguments = FrontendUtility::getArguments();
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
    }

    /**
     * @return void
     */
    public function initializeSpamCheck(): void
    {
    }

    /**
     * Example spamcheck, return true if spam recocnized
     *
     * @return bool
     */
    public function spamCheck(): bool
    {
        return false;
    }
}
