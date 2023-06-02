<?php

declare(strict_types=1);
namespace Qc\QcComments\SpamShield\Methods;
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
