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
