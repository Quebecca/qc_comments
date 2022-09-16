<?php

namespace Qc\QcComments\Controller\Frontend;

/***
 *
 * This file is part of Qc Comments project.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2022 <techno@quebec.ca>
 *
 ***/

use Qc\QcComments\Domain\Model\Comment;
use Qc\QcComments\Domain\Repository\CommentRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

// FrontEnd Controller
class CommentsController extends ActionController
{
    // @Todo : Add filter by util
    // @Todo : Template for public part
    // @Todo : rendre le nombre de caractères dynamique (en affichage aussi) dans le FE (config typoscript)
    // @Todo : Test on typo3 v11
    // @Todo : Recaptcha, Utilisation des Unix timestamp(Modify export task for map the date column )
    // @Todo : Security Test XSS (il faut pas utiliser f:format.raw),
    // @Todo : SQL injection (on enregistre avec PersistenceManagerInterface) ...  OWASP ZAP, Burpsuit


    /**
     * @var CommentRepository
     */
    protected CommentRepository $commentsRepository;

    public function injectCommentsRepository(CommentRepository $commentsRepository)
    {
        $this->commentsRepository = $commentsRepository;
    }

    /**
     * This function is used to render comments form
     * @param array $args
     */
    public function showAction(array $args = [])
    {
        $this->view->assignMultiple([
            'submitted' => $this->request->getArguments()['submitted'],
            'comment' => new Comment()
        ]);
    }

    /**
     * This function is used to save user comment
     * @param Comment|null $comment
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     */
    public function saveCommentAction(Comment $comment = null)
    {
        if ($comment) {
            $pageUid = $comment->getUidOrig();
            // @todo : should store the absolute url or uri
            $comment->setUidPermsGroup(
                BackendUtility::getRecord('pages', $pageUid, 'perms_groupid', "uid = $pageUid")['perms_groupid']
            );
            // set limit for 500 characters
            $comment->setComment(substr($comment->getComment(), 0, 500));
            $comment->setDateHoure(date('Y-m-d H:i:s'));
            $this->commentsRepository->add($comment);
        }
        $this->forward('show', null, null, ['submitted' => true]);
    }
}
