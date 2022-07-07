<?php

namespace Qc\QcComments\Controller\Frontend;

use Qc\QcComments\Domain\Model\Comment;
use Qc\QcComments\Domain\Repository\CommentRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

class CommentsController extends ActionController
{
    protected CommentRepository $commentsRepository;
    public function injectCommentsRepository(CommentRepository $commentsRepository){
        $this->commentsRepository = $commentsRepository;
    }

    public function showAction(array $args = []){
        $comment = new Comment();
        $this->view->assignMultiple([
            "submitted" => $this->request->getArguments()['submitted'],
            "comment" => $comment
        ]);
    }

    /**
     * @param Comment|null $comment
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     * @throws AspectNotFoundException
     */
    public function addCommentAction(Comment $comment = null){
        $context = GeneralUtility::makeInstance(Context::class);
        debug($context->getPropertyFromAspect('backend.user', 'groupIds'));
        $pageUid = $comment->getUidOrig();
        $comment->setUidPermsGroup(
            BackendUtility::getRecord('pages', $pageUid, 'perms_groupid', "uid = $pageUid")['perms_groupid']
        );
        $this->commentsRepository->add($comment);
        $this->forward('show',null, null, ['submitted' => true]);
    }

}