<?php

declare(strict_types=1);
namespace Qc\QcComments\SpamValidator\SpamShield;

use Qc\QcComments\Domain\Model\Comment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class HoneyPodMethod
 */
class HoneyPodMethod extends AbstractMethod
{
    /**
     * Honeypod Check: Spam recognized if Honeypod field is filled
     *
     * @return bool true if spam recognized
     */
    public function spamCheck(Comment $comment = null): bool
    {
        return !empty($this->getArgumentsFromGetOrPostRequest("tx_qccomments_commentsform"));
    }
    /**
     * @param string $key
     * @return string
     */
    protected function getArgumentsFromGetOrPostRequest(string $key): string
    {
        $args = (array)GeneralUtility::_GP('tx_qccomments_commentsform');
        return $args['field']['__hp'];
    }
}
