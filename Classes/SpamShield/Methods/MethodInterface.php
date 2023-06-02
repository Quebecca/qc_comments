<?php

declare(strict_types=1);
namespace Qc\QcComments\SpamShield\Methods;

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
     * @param Comment $comment
     * @return bool
     */
    public function spamCheck(Comment $comment): bool;
}
