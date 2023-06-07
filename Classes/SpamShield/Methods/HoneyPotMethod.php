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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class HoneyPodMethod
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
        $args = (array)GeneralUtility::_GP('tx_qccomments_commentsform');
        return !empty($args['field']['__hp']);
    }
}
