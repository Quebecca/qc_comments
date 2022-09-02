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
    // @Todo : FrontEnd form rendering configuration
    // @Todo : Database migration for old records - Script to change the old table structure
    // @Todo : Backend tabs CSS bugs
    // @Todo : Security Test XSS, SQL injection ...  OWASP ZAP, Burpsuit

    /**
     * @var CommentRepository
     */
    protected CommentRepository $commentsRepository;

    public function injectCommentsRepository(CommentRepository $commentsRepository){
        $this->commentsRepository = $commentsRepository;
    }

    public function showAction(array $args = []){
        $this->view->assign( "submitted",$this->request->getArguments()['submitted']);
    }

    /**
     * @param Comment $comment
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     */
    public function saveCommentAction(Comment $comment){
        $pageUid = $comment->getUidOrig();
        // @todo : should store the absolute url or uri
        $comment->setUidPermsGroup(
            BackendUtility::getRecord('pages', $pageUid, 'perms_groupid', "uid = $pageUid")['perms_groupid']
        );
        $comment->setDateHoure(date('Y-m-d H:i:s'));
        $this->commentsRepository->add($comment);

        $this->forward('show',null, null, ['submitted' => true]);
    }

}