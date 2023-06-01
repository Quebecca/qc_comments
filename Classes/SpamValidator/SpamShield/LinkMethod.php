<?php

declare(strict_types=1);
namespace Qc\QcComments\SpamValidator\SpamShield;


use Qc\QcComments\Domain\Model\Comment;

/**
 * Class LinkMethod
 */
class LinkMethod extends AbstractMethod
{
    /**
     * Link Check: Counts numbers of links in message
     *
     * @return bool true if spam recognized
     */
    public function spamCheck(Comment $comment): bool
    {
        preg_match_all('@http://|https://|ftp://@', $comment->getComment(), $result);
        return count($result[0]) > (int)$this->configuration['linkLimit'];
    }
}
