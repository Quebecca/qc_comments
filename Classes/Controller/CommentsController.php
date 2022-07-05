<?php

namespace Qc\QcComments\Controller;

use Qc\QcComments\Domain\Model\Comment;
use Qc\QcComments\Domain\Repository\CommentRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

class CommentsController extends ActionController
{
    protected CommentRepository $commentsRepository;

    public function injectCommentsRepository(CommentRepository $commentsRepository){
        $this->commentsRepository = $commentsRepository;
    }

    /**
     * @throws IllegalObjectTypeException
     */
    public function showAction(Comment $comment = null){
        if($comment == null)
            $comment = new Comment();
        else{
            $this->view->assign("submittedComment", true);
            $this->commentsRepository->add($comment);
        }
    }

}