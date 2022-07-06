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
    
    public function showAction(){
        /*if($comment == null)
            $comment = new Comment();
        else{
            $this->view->assign("submittedComment", true);
        }*/
    }

    /**
     * @param Comment|null $comment
     * @throws IllegalObjectTypeException
     */
    public function addCommentAction(Comment $comment = null){
        $this->commentsRepository->add($comment);
        debug('Saving comment!!!!');
        // forward vers show
    }

}