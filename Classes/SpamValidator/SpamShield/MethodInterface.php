<?php

declare(strict_types=1);
namespace Qc\QcComments\SpamValidator\SpamShield;

use Qc\QcComments\Domain\Model\Comment;

/**
 * Interface MethodInterface
 */
interface MethodInterface
{
    /**
     * @param Comment $comment
     * @param array $settings
     * @param array $configuration
     */
    public function __construct(Comment $comment, array $settings,  array $configuration = []);

    /**
     * @return void
     */
    public function initialize(): void;

    /**
     * @return void
     */
    public function initializeSpamCheck(): void;

    /**
     * @return bool
     */
    public function spamCheck(Comment $comment): bool;
}
