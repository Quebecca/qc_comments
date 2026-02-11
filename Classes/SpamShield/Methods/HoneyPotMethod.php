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
 * Class HoneyPotMethod
 */
class HoneyPotMethod extends AbstractMethod
{
    /**
     * Honeypot Check: Spam recognized if Honeypot field is filled
     *
     * @return bool true if spam recognized
     */
    public function spamCheck(Comment $comment = null): bool
    {
        $request ??= $GLOBALS['TYPO3_REQUEST'] ?? null;
        $args = (array)$request->getParsedBody()['tx_qccomments_commentsform'] ?? null;
        if($args === null) { return false; }

        return !empty($args['field']['__hp']);
    }
}
