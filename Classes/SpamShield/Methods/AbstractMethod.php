<?php

declare(strict_types=1);
namespace Qc\QcComments\SpamShield\Methods;

use Qc\QcComments\Domain\Model\Comment;

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
    public function __construct(Comment $comment, array $settings = [], array $configuration = [])
    {
        $this->comment = $comment;
        $this->settings = $settings;
        $this->configuration = $configuration;
    }

    /**
     * @param Comment $comment
     * @return bool*
     */
    public function spamCheck(Comment $comment): bool
    {
        return false;
    }
}
